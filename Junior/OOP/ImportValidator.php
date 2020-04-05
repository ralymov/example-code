<?php

namespace App\Models\Import;


interface ImportValidator {

    /**
     * @param $data mixed
     * @return boolean
     */
    function validate($data);
}