<?php

namespace App\Models\Import;

interface ImportApi {
    public function getData();

    public function getValidator(): ImportValidator;

    public function getConverter(): ImportConverter;
}
