<?php

namespace App\Models\Import;

interface ImportConverter {

    /**
     * Returns lead form source data
     * @param $data mixed
     * @return LoanWrapper
     */
    function convert($data): LoanWrapper;
}
