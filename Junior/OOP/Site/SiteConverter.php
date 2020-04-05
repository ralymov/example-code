<?php

namespace App\Models\Import\Site;

use App\Helpers\StringHelper;
use App\Models\Import\ImportConverter;
use App\Models\Import\LoanWrapper;
use App\Models\Storage\Address\Address;
use App\Models\Storage\Address\PhoneRegion;
use App\Models\Storage\Contractor\Contractor;
use App\Models\Storage\Contractor\ContractorType;
use App\Models\Storage\Contractor\NaturalPerson;
use App\Models\Storage\Info\Contact;
use App\Models\Storage\Loan\CarOrder;
use App\Models\Storage\Loan\FinancialInstrument;
use App\Models\Storage\Loan\Loan;
use App\Models\Storage\Loan\LoanStatus;
use App\Models\Storage\Loan\Reference\TreatmentType;
use App\Models\Storage\Loan\Source\DataSource;
use App\Models\Storage\Loan\TechParam;
use App\Models\Storage\Showroom\AutolocalCar;
use App\Models\Storage\User\Person;
use App\Models\Storage\User\Reference\ContactType;
use App\Models\Storage\Vehicle\Brand;
use App\Models\Storage\Vehicle\Complectation;
use App\Models\Storage\Vehicle\Modification;
use App\Models\Storage\Vehicle\Series;

class SiteConverter implements ImportConverter {

    public $loan_wrapper;
    private $dataSource;

    public function __construct(DataSource $dataSource = null) {
        $this->loan_wrapper = new LoanWrapper();
        $this->dataSource = $dataSource;
    }

    /**
     * @param mixed $data
     * @return LoanWrapper
     */
    public function convert($data): LoanWrapper {
        $loan = new Loan();
        $person = new Person();
        $contractor = new Contractor();
        $naturalPerson = new NaturalPerson();
        $address = new Address();
        $techParam = new TechParam();
        $financialInstrument = new FinancialInstrument();

        $this->importAddress($data, $address);
        $this->importPerson($data, $person);
        $this->importContractor($contractor);
        $this->importNaturalPerson($naturalPerson);
        $this->importTechParams($data, $techParam);
        $this->importFinancialInstrument($data, $financialInstrument);
        $this->importContacts($data);
        $this->importLoan($data, $loan);
        $this->detectCar($data);

        return $this->loan_wrapper;
    }

    private function importLoan($data, $loan): void {
        $loan->treatment_type_id = TreatmentType::LOAN;
        $loan->status_id = LoanStatus::new()->first()->id ?? null;
        $this->loan_wrapper->loan = $loan;
    }

    private function detectCar($data): void {
        $modification = null;
        $complectation = null;
        $brand = null;
        $series = null;
        $detectedCar = null;

        if (!empty($data['brand_sync_alias'])) {
            $brand = Brand::where('alias', 'ILIKE', $data['brand_sync_alias'])->first();
        } elseif (!empty($data['brand'])) {
            $brand = Brand::where('title', 'ILIKE', $data['brand'])->first();
        }

        if (!empty($data['model_sync_alias'])) {
            $series = Series::where('clear_alias', 'ILIKE', $data['model_sync_alias'])->first();
        } elseif (!empty($data['model'])) {
            $series = Series::where('title', 'ILIKE', $data['model'])->first();
        }

        if (!empty($data['modification_sync_id'])) {
            $modification = Modification::where('external_id', $data['modification_sync_id'])->first();
        }
        if (!empty($data['complectation_sync_id'])) {
            $complectation = Complectation::where('external_id', $data['complectation_sync_id'])->first();
        }

        if ($brand && $series && $modification && $complectation && $this->dataSource->salon_id) {
            $detectedCar = AutolocalCar::where([
                ['brand_id', $brand->id],
                ['series_id', $series->id],
                ['modification_id', $modification->id],
                ['complectation_id', $complectation->id],
                ['salon_id', $this->dataSource->salon->id ?? null]
            ])->first();
        }

        $carOrder = new CarOrder([
            'brand_id' => $brand->id ?? null,
            'series_id' => $series->id ?? $modification->series_id ?? $complectation->series_id ?? null,
            'modification_id' => $modification->id ?? null,
            'complectation_id' => $complectation->id ?? null,
            'autolocal_car_id' => $detectedCar->id ?? null,
            'brand_text' => $data['brand'] ?? null,
            'series_text' => $data['model'] ?? null,
            'complectation_text' => $data['complectation'] ?? null,
            'price' => $data['car_cost'] ?? null,
            'year' => (int)($data['model_year'] ?? null),
            'mileage' => $data['mileage'] ?? null,
            'image' => $data['used_image'] ?? null,
            'used' => ($data['model_year'] ?? null) || ($data['used'] ?? null)
        ]);
        $this->loan_wrapper->car_order = $carOrder;
    }

    private function importPerson($data, Person $person): void {
        $person->last_name = $data['last_name'] ?? null;
        $person->first_name = $data['first_name'] ?? null;
        $person->middle_name = $data['middle_name'] ?? null;
        $person->age = $data['age'] ?? null;
        $person->birth_date = $data['birthdate'] ?? null;

        if (!empty($data['fio'])) {
            $fio = explode(' ', trim($data['fio']));
            $person->last_name = $fio[0] ?? null;
            $person->first_name = $fio[1] ?? null;
            $person->middle_name = $fio[2] ?? null;
        }

        $this->loan_wrapper->person = $person;
    }

    private function importContractor(Contractor $contractor): void {
        $contractor->type_id = ContractorType::natural;
        $this->loan_wrapper->contractor = $contractor;
    }

    private function importNaturalPerson(NaturalPerson $natural_person): void {
        $this->loan_wrapper->natural_person = $natural_person;
    }

    private function importAddress($data, Address $address): void {
        if (!empty($data['place_reg'])) {
            $address->region = $data['place_reg'];
            $address->region_kladr_id = detectRegion(null, $data['place_reg'])->kladr_id ?? null;
        } elseif (!empty($data['mobile_tel'])) {
            $region = PhoneRegion::detect($data['mobile_tel']);
            $address->region = optional($region)->name;
            $address->region_kladr_id = optional($region)->kladr_id;
        }
        $this->loan_wrapper->address = $address;
    }

    private function importContacts($data): void {
        if (!empty($data['mobile_tel'])) {
            $phone_contact = new Contact();
            $phone_contact->type_id = ContactType::PHONE;
            $phone_contact->value = StringHelper::phoneClean($data['mobile_tel']);
            $this->loan_wrapper->phone_contact = $phone_contact;
        }

        if (!empty($data['email'])) {
            $email_contact = new Contact();
            $email_contact->type_id = ContactType::EMAIL;
            $email_contact->value = $data['email'];
            $this->loan_wrapper->email_contact = $email_contact;
        }
    }

    private function importTechParams($data, TechParam $tech_param): void {
        $tech_param->json_loan = json_encode($data);
        $tech_param->ip_address = $data['ip_address'] ?? null;
        $tech_param->referer = $data['referer'] ?? null;
        $tech_param->additional_params = null;
        if (isset($data['additional_params'])) {
            $tech_param->additional_params =
                (\is_array($data['additional_params']) ? json_encode($data['additional_params']) : $data['additional_params'])
                ?? null;
        }
        $tech_param->comment = $data['comment'] ?? null;
        $tech_param->ad_channel = $data['ad_channel'] ?? null;
        $tech_param->created = $data['created'] ?? null;
        $tech_param->phone_region = $this->loan_wrapper->address->region ?? null;
        $tech_param->parseUtmMarks();
        $tech_param->parseAirbridgeId($data);
        $this->loan_wrapper->tech_param = $tech_param;
    }

    private function importFinancialInstrument($data, FinancialInstrument $financial_instrument): void {
        $financial_instrument->client_initial_fee = filter_var($data['inital_pay'] ?? null, FILTER_SANITIZE_NUMBER_INT);
        $financial_instrument->client_initial_fee = $financial_instrument->client_initial_fee === ''
            ? null : (int)$financial_instrument->client_initial_fee;

        $financial_instrument->credit_term = filter_var($data['credit_term'] ?? null, FILTER_SANITIZE_NUMBER_INT);
        $financial_instrument->credit_term = (int)$financial_instrument->credit_term >= 12
            ? (int)$financial_instrument->credit_term
            : (int)$financial_instrument->credit_term * 12;
        $financial_instrument->credit_term = $financial_instrument->credit_term === 0
            ? null : (int)$financial_instrument->credit_term;

        $financial_instrument->loan_object_value = (int)($data['car_cost'] ?? null);
        $financial_instrument->full_loan_value = (int)($data['car_cost'] ?? null);
        $matches = [];
        preg_match('/Кредитная ставка\: (\d+\.\d+%)/u', $data['comment'] ?? '', $matches);
        if (isset($matches[1])) {
            $financial_instrument->client_interest_rate = (float)($matches[1] ?? null);
        }
        $matches = [];
        preg_match('/Платеж\: ([0-9 ]+руб\.)/u', $data['comment'] ?? '', $matches);
        if (isset($matches[1])) {
            $financial_instrument->client_monthly_payment = filter_var($matches[1] ?? null, FILTER_SANITIZE_NUMBER_INT) ?? null;
        }
        $this->loan_wrapper->financial_instrument = $financial_instrument;
    }

}
