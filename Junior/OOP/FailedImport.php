<?php

namespace App\Models\Import;

use App\Models\Storage\Loan\Source\DataSource;
use Illuminate\Database\Eloquent\Model;

class FailedImport extends Model {

    protected $guarded = ['id'];

    public function data_source() {
        return $this->belongsTo(DataSource::class);
    }

}
