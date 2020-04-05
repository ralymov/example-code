<?php


namespace App\Models\Import\Comparator;


use App\Models\Import\LoanWrapper;
use App\Models\Storage\Loan\LoanView;

class LoanComparator {

    public static function compareVersions(LoanView $old, LoanWrapper $new) {
        $result = [];
        foreach ($new->model_fields as $key => $value) {
            $path_to_field = explode('->', $value);
            $path_one = $path_to_field[0];
            $path_two = $path_to_field[1];

            if (!isset($new->$path_one->$path_two) && !isset($old->$key)) {
                continue;
            }
            if (is_null($old->$key) && !is_null($new->$path_one->$path_two)) {
                $result[$key] = $new->$path_one->$path_two;
            }
        }
        return $result;
    }

}