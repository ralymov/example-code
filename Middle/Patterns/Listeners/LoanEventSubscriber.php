<?php

namespace App\Listeners;


use App\Events\Common\ModelCreated;
use App\Events\Common\ModelSaved;
use App\Events\FinancialInstrument\FinancialInstrumentSaved;
use App\Events\Loan\LoanCreated;
use App\Events\Loan\LoanSaved;
use App\Http\Resources\Import\DataSource;
use App\Models\Storage\Contractor\SalonAppealsHistory;
use App\Models\Storage\Loan\AppealHistory;
use App\Models\Storage\Loan\AppealHistoryType;
use App\Models\Storage\Loan\CarSelectHistory;
use App\Models\Storage\Loan\ChainOfStaff;
use App\Models\Storage\Loan\FinancialInstrumentChangeHistory;
use App\Models\Storage\Loan\Loan;
use App\Models\Storage\Loan\LoanConversionLine;
use App\Models\Storage\Loan\LoanStatus;
use App\Models\Storage\Loan\LoanStatusHistory;
use App\Models\Storage\Loan\Reference\LoanStage;
use App\Models\Storage\Loan\Source\DataSourceType;
use App\Models\Storage\Showroom\Salon;
use App\Models\Storage\Showroom\SalonChangeHistory;
use Carbon\Carbon;
use Log;

class LoanEventSubscriber {

    public function onLoanChangeStatus($event) {
        if ($event->loan->wasRecentlyCreated && $event->loan->status_id) {
            LoanStatusHistory::create(
                [
                    'loan_id' => $event->loan->id,
                    'type_id' => $event->loan->loan_type_id,
                    'status_id' => $event->loan->status_id,
                    'user_id' => optional(auth()->user())->id,
                ]
            );
        } elseif (isset($event->loan->getChanges()['status_id'])) {
            LoanStatusHistory::create(
                [
                    'loan_id' => $event->loan->id,
                    'type_id' => $event->loan->loan_type_id,
                    'status_id' => $event->loan->getChanges()['status_id'],
                    'user_id' => optional(auth()->user())->id,
                    'old_status_id' => $event->loan->getOriginal()['status_id'],
                ]
            );
            if ($event->loan->salon_id && Salon::find($event->loan->salon_id) &&
                LoanStatus::find($event->loan->getChanges()['status_id'])->isSalonAppeal()) {
                SalonAppealsHistory::create([
                    'date' => Carbon::now(),
                    'contractor_id' => $event->loan->contractor_id,
                    'loan_id' => $event->loan->id,
                    'salon_id' => $event->loan->salon_id,
                    'showroom_id' => $event->loan->showroom_id,
                ]);
            }
        }
    }

    public function onLoanChangeSalon($event) {
        if ($event->loan->wasRecentlyCreated && $event->loan->salon_id) {
            SalonChangeHistory::create([
                'loan_id' => $event->loan->id,
                'salon_id' => $event->loan->salon_id,
                'employee_id' => auth()->user()->employee->id ?? null,
            ]);
        } elseif (
            isset($event->loan->getOriginal()['salon_id'], $event->loan->getChanges()['salon_id']) &&
            $event->loan->getOriginal()['salon_id'] !== $event->loan->getChanges()['salon_id']
        ) {
            SalonChangeHistory::create([
                'loan_id' => $event->loan->id,
                'old_salon_id' => $event->loan->getOriginal()['salon_id'],
                'salon_id' => $event->loan->getChanges()['salon_id'],
                'employee_id' => auth()->user()->employee->id ?? null,
            ]);
        } elseif ($event->loan->sold) {
            SalonChangeHistory::create([
                'loan_id' => $event->loan->id,
                'salon_id' => $event->loan->salon_id,
                'employee_id' => auth()->user()->employee->id ?? null,
                'sold' => true,
            ]);
        }
    }

    public function onLoanCreated($event) {
        $loan = $event->loan;
        $loanFromSite = ($loan->data_source->type->code ?? null) !== DataSourceType::ASTERISK;
        if ($loanFromSite) {
            $appealHistoryTypeId = AppealHistoryType::getApplicationType()->id;
        }

        AppealHistory::create([
            'loan_id' => $loan->id,
            'contractor_id' => $loan->contractor_id,
            'is_order' => (bool)$loan->is_order,
            'car_name' => $loan->car_order->full_name_with_price ?? null,
            'source_created_at' => $loan->tech_param->created ?? null,
            'appeal_history_type_id' => $appealHistoryTypeId ?? null
        ]);
        $loan->generateOrderNumber();
    }


    public function subscribe($events) {
        $events->listen(
            LoanSaved::class,
            'App\Listeners\LoanEventSubscriber@onLoanChangeStatus'
        );
        $events->listen(
            LoanCreated::class,
            'App\Listeners\LoanEventSubscriber@onLoanCreated'
        );
//        $events->listen(
//            LoanSaved::class,
//            'App\Listeners\LoanEventSubscriber@onLoanChangeSalon'
//        );
//        $events->listen(
//            FinancialInstrumentSaved::class,
//            'App\Listeners\LoanEventSubscriber@onFinancialInstrumentChange'
//        );
//        $events->listen(
//            LoanSaved::class,
//            'App\Listeners\LoanEventSubscriber@onLoanChainOfStaff'
//        );
//        $events->listen(
//            LoanSaved::class,
//            'App\Listeners\LoanEventSubscriber@onLoanConversionChange'
//        );
//        $events->listen(
//            LoanSaved::class,
//            'App\Listeners\LoanEventSubscriber@onLoanChangeCar'
//        );
    }

    public function onLoanChangeCar($event) {
        if (isset($event->loan->getChanges()['warehouse_item_id'])) {
            CarSelectHistory::create(
                [
                    'new_car_id' => $event->loan->getChanges()['warehouse_item_id'],
                    'old_car_id' => $event->loan->getOriginal()['warehouse_item_id'],
                    'change_reason' => '',
                    'loan_id' => $event->loan->id,
                    'stage_id' => $event->loan->stage_id,
                ]
            );
        }
    }

    public function onLoanConversionChange($event) {
        if ($event->loan->wasRecentlyCreated) {
            LoanConversionLine::create(
                [
                    'loan_id' => $event->loan->id,
                    'stage_id' => LoanStage::STAGE_A,
                ]
            );
        }
        if (isset($event->loan->getChanges()['stage_id'])) {
            LoanConversionLine::create(
                [
                    'loan_id' => $event->loan->id,
                    'stage_id' => $event->loan->stage_id,
                ]
            );
        }
    }

    public function onFinancialInstrumentChange($event) {
        $observed_fields = [
            'credit_term',
            'client_interest_rate',
            'client_initial_fee',
            'full_loan_value',
            'client_monthly_payment'
        ];
        if (is_array_keys_exists($observed_fields, $event->financial_instrument->getChanges())) {
            FinancialInstrumentChangeHistory::create(
                [
                    'financial_instrument_id' => $event->financial_instrument->id,
                    'credit_term' => $event->financial_instrument->credit_term,
                    'client_interest_rate' => $event->financial_instrument->client_interest_rate,
                    'client_initial_fee' => $event->financial_instrument->client_initial_fee,
                    'full_loan_value' => $event->financial_instrument->full_loan_value,
                    'client_monthly_payment' => $event->financial_instrument->client_monthly_payment,
                    'author_id' => auth()->user()->id,
                ]
            );
        }
    }

    public function onLoanChainOfStaff($event) {
        if ($event->loan->getChanges() && auth()->user()) {
            ChainOfStaff::create(
                [
                    'user_id' => auth()->user()->id,
                    'loan_id' => $event->loan->id,
                    'stage_id' => $event->loan->stage_id,
                ]
            );
        }
    }


}
