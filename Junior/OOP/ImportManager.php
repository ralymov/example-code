<?php

namespace App\Models\Import;

use App\Facades\Log\ImportLog;
use App\Helpers\StringHelper;
use App\Models\Storage\Loan\Source\DataSource;
use App\Services\Distribution\DistributionService;
use App\Services\Loan\Source\DetectBlacklistSourceService;
use App\Services\Sorter\DetectExistingContractorService;
use App\Services\Sorter\DuplicationProtectionService;
use App\Services\Source\DetectSourceService;
use Carbon\Carbon;

class ImportManager {

    /**
     * @var DataSource $data_source
     */
    public $data_source;
    public $currentItem;

    /**
     * Validate and save lead
     * @param DataSource $dataSource
     * @throws \Exception
     */
    public function import(DataSource $dataSource): void {
        ImportLog::info("Import from {$dataSource->name} started");
        $this->data_source = $dataSource;
        $import = $this->data_source->getImportSource();
        if (!$import) ImportLog::info("Import from {$dataSource->name} failed. No ImportSource for this type.");

        $data = $import->getData();
        $validator = $import->getValidator();
        $converter = $import->getConverter();
        if (empty($data)) {
            ImportLog::info("Empty data while import from {$dataSource->name}");
            return;
        }

        foreach ($data as $item) {
            try {
                if ($validator->validate($item)) {
                    $this->currentItem = $item;
                    $loanWrapper = $converter->convert($item);
                    $this->importLoan($loanWrapper);
                }
            } catch (\Throwable $ex) {
                FailedImport::create(['data' => json_encode($item), 'data_source_id' => $dataSource->id]);
                app('sentry')->captureException($ex);
                ImportLog::error($ex->getMessage());
                ImportLog::error($ex->getTraceAsString());
            }
        }

        ImportLog::info("Import from {$dataSource->name} finished");
    }

    public function importLoan(LoanWrapper $loanWrapper, bool $updateLast = true): void {
        $loanWrapper->loan->data_source_id = $this->data_source->id;
        $loanWrapper->loan->salon_id = $this->data_source->salon_id ?? null;
        $loanWrapper = DetectExistingContractorService::handle($loanWrapper);
        $loanWrapper = DuplicationProtectionService::handle($loanWrapper, $this->data_source);
        $loanWrapper = DistributionService::handle($loanWrapper);
        DetectBlacklistSourceService::handle($loanWrapper, $this->currentItem);

        ImportLog::debug('Searching for loan data_source: ' . $this->data_source->id
            . ' phone: ' . StringHelper::phoneClean($loanWrapper->phone_contact->value)
            . ' created: ' . Carbon::today());

        $saved = $loanWrapper->saveLoan();
        if ($saved) {
            ImportLog::info("Imported new loan {$saved->id}");
        } else {
            ImportLog::error('Failed to import loan' . optional($saved->phones())->first()->value);
        }

        if (!$updateLast) return;
        $created = new \Datetime($loanWrapper->tech_param->created);
        $this->data_source->last = $created;
        $this->data_source->save();
    }

    public function importSingleFailed(FailedImport $failedImport): void {
        $this->data_source = $failedImport->data_source;
        $import = $this->data_source->getImportSource();
        if ($import) {
            $item = json_decode($failedImport->data, true);
            $validator = $import->getValidator();
            $converter = $import->getConverter();
            if (empty($item)) {
                return;
            }
            try {
                if ($validator->validate($item)) {
                    $this->currentItem = $item;
                    $loan_wrapper = $converter->convert($item);
                    $this->importLoan($loan_wrapper, false);
                    $failedImport->delete();
                }
            } catch (\Throwable $ex) {
                app('sentry')->captureException($ex);
                ImportLog::error($ex->getMessage());
                ImportLog::error($ex->getTraceAsString());
            }
        }
    }

    public function importAllFailed(): void {
        $data = FailedImport::all();
        foreach ($data as $item) {
            $itemData = json_decode($item->data, true);
            $this->data_source = $item->data_source;
            $import = $this->data_source->getImportSource();
            if ($import) {
                $validator = $import->getValidator();
                $converter = $import->getConverter();
                try {
                    if ($validator->validate($itemData)) {
                        $this->currentItem = $itemData;
                        $loan_wrapper = $converter->convert($itemData);
                        $this->importLoan($loan_wrapper, false);
                        $item->delete();
                    }
                } catch (\Throwable $ex) {
                    app('sentry')->captureException($ex);
                    ImportLog::error($ex->getMessage());
                    ImportLog::error($ex->getTraceAsString());
                }
            }
        }
    }

}
