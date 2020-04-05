<?php

namespace App\Models\Import;

use App\Facades\Log\ImportLog;
use App\Models\Storage\Contractor\Contractor;
use App\Models\Storage\Contractor\NaturalPerson;
use App\Models\Storage\Info\Contact;
use App\Models\Storage\Loan\CarOrder;
use App\Models\Storage\Loan\Loan;
use App\Models\Storage\Loan\TechParam;
use App\Models\Storage\User\Person;
use DB;
use Illuminate\Database\Eloquent\Model;

class LoanWrapper extends Model {

    public $loan;
    public $car_order;
    public $person;
    public $natural_person;
    public $contractor;
    public $address;
    public $tech_param;
    public $financial_instrument;
    public $phone_contact;
    public $email_contact;
    public $call;

    public $model_fields = [
        'brand_id' => 'car_loan->brand_id',
        'series_id' => 'car_loan->series_id',
        'year' => 'car_loan->year',
        'price' => 'car_loan->price',
        'modification_text' => 'car_loan->modification_text',
        'last_name' => 'person->last_name',
        'first_name' => 'person->first_name',
        'middle_name' => 'person->middle_name',
        'region' => 'address->region',
        'age' => 'person->age',
        'phone' => 'phone_contact->value',
        'email' => 'email_contact->value',
        'referer' => 'tech_param->referer',
        'additional_params' => 'tech_param->additional_params',
        'ip_address' => 'tech_param->ip_address',
        'birth_date' => 'passport->birth_date',
        'created' => 'tech_param->created',
        'ad_channel' => 'tech_param->ad_channel',
        'json_loan' => 'tech_param->json_loan',
    ];

    public function __construct(Contact $phone_contact = null,
                                Person $person = null,
                                Contractor $contractor = null,
                                NaturalPerson $natural_person = null,
                                TechParam $tech_param = null,
                                Loan $loan = null,
                                CarOrder $carOrder = null,
                                $call = null) {
        parent::__construct();
        $this->phone_contact = $phone_contact;
        $this->person = $person;
        $this->contractor = $contractor;
        $this->natural_person = $natural_person;
        $this->tech_param = $tech_param;
        $this->loan = $loan;
        $this->call = $call;
        $this->car_order = $carOrder;
    }

    public function saveLoan(): Loan {
        if ($this->loan->is_duplicate) {
            return $this->saveDuplicateLoan();
        }
        if ($this->loan->contractor_id) {
            return $this->saveWithExistingContractor();
        }
        return $this->saveNewLoan();
    }

    private function saveNewLoan(): Loan {
        DB::transaction(function () {
            $this->saveModel('address', ['person'], 'residential_');
            $this->saveModel('person', ['natural_person']);
            $this->saveModel('contractor', ['natural_person', 'loan']);
            $this->saveModel('natural_person', []);
            $this->saveModel('tech_param');
            $this->saveModel('car_order');
            $this->saveModel('financial_instrument');
            $this->saveModel('loan');

            $this->savePhone();
            $this->saveEmail();
        });
        return $this->loan;
    }

    private function saveWithExistingContractor(): Loan {
        ImportLog::info("Import loan with existing contractor (original contractor id - {$this->loan->contractor_id})");
        DB::transaction(function () {
            $this->saveModel('tech_param');
            $this->saveModel('car_order');
            $this->saveModel('loan');
        });
        return $this->loan;
    }

    private function saveDuplicateLoan(): Loan {
        $originalLoan = Loan::findOrFail($this->loan->original_id);
        ImportLog::info("Import duplicate loan (original loan id - $originalLoan->id)");
        $this->loan->contractor_id = $originalLoan->contractor_id;
        DB::transaction(function () {
            $this->saveModel('tech_param');
            $this->saveModel('car_order');
            $this->saveModel('loan');
        });
        return $this->loan;
    }

    public function saveCall(): ?Loan {
        if ($this->isAnswered($this->call)) return null;
        $this->person->last_name = 'Уточнить';
        //$unansweredCall->loan_id = optional($loan)->id;
        //$unansweredCall->call_date = $this->call['calldate'];
        //$unansweredCall->caller_number = $this->call['src_right'];
        //$unansweredCall->dst_right = $this->call['dst_right'];
        //$unansweredCall->disposition = $this->call['disposition'];
        //$unansweredCall->channel = $this->call['channel'];
        //$unansweredCall->dst_channel = $this->call['dstchannel'];
        //$unansweredCall->last_app = $this->call['lastapp'];
        //$unansweredCall->did = $this->call['did'];
        //$unansweredCall->recording_file = $this->call['recordingfile'];
        //$unansweredCall->save();
        return $this->saveLoan();
    }

    private function saveModel(string $modelName = null,
                               array $intermediateModelNames = ['loan'],
                               string $modelNamePrefix = ''): void {
        optional($this->$modelName)->setAppends([]);
        if (!$modelName || !$this->$modelName || !isArrayHaveNotNullFields($this->$modelName->toArray())) return;
        $this->$modelName->save();

        foreach ($intermediateModelNames as $intermediateModelName) {
            $this->$intermediateModelName->{$modelNamePrefix . $modelName . '_id'} = $this->$modelName->id ?? null;
        }
    }

    private function savePhone(): void {
        if (!$this->phone_contact) return;
        $this->phone_contact->save();
        $this->loan->contacts()->attach($this->phone_contact->id);
        $this->loan->contractor->contacts()->attach($this->phone_contact->id);
    }

    private function saveEmail(): void {
        if (!$this->email_contact) return;
        $this->email_contact->save();
        $this->loan->contacts()->attach($this->email_contact->id);
        $this->loan->contractor->contacts()->attach($this->email_contact->id);
    }

    private function isAnswered($call): bool {
        return \strlen($call['src_right']) === 10 &&
            \strlen($call['dst_right']) === 3 &&
            $call['lastapp'] !== 'Queue' &&
            $call['disposition'] === 'ANSWERED';
    }

}
