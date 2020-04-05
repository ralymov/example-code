<?php

namespace App\Models\Import\Site;

use App\Models\Import\ImportValidator;
use App\Facades\Log\ImportLog;
use Validator;

class SiteValidator implements ImportValidator {

    /**
     * @param $data mixed
     * @return boolean
     * @throws \Exception
     */
    public function validate($data) {
        $validator = Validator::make($data, [
            'brand' => 'nullable|max:255',
            'model' => 'nullable|max:255',
            'model_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'car_cost' => 'nullable|integer',
            'inital_pay' => 'nullable',
            'fio' => 'required_without_all:last_name,first_name,middle_name|max:255',
            'last_name' => 'required_without:fio|max:255',
            'first_name' => 'nullable|max:255',
            'middle_name' => 'nullable|max:255',
            'place_reg' => 'nullable|max:255',
            'age' => 'nullable|integer',
            'mobile_tel' => 'required|max:255',
            'email' => 'nullable|max:255',
            'referer' => 'nullable',
            'additional_params' => 'nullable',
            'ip_address' => 'nullable|max:255',
            'credit_term' => 'nullable|max:255',
            'comment' => 'nullable',
            'complectation' => 'nullable|max:255',
            'birthdate' => 'nullable|date',
            'created' => 'nullable|date_format:Y-m-d H:i:s',
            'work_income' => 'nullable',
            'ad_channel' => 'nullable|max:255',
            'airbridge_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                ImportLog::error($error);
            }
            return false;
        }
        return true;
    }
}
