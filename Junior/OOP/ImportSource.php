<?php

namespace App\Models\Import;

/**
 * Description of Import
 *
 */
abstract class ImportSource implements ImportApi {

    protected $source;

    public function __construct($source) {
        $this->source = $source;
    }
}
