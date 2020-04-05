<?php

namespace App\Events\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;

class ModelCreated {

    use SerializesModels;

    public $model;

    /**
     * Create a new event instance.
     *
     * @param Model $model
     *
     */
    public function __construct(Model $model) {
        $this->model = $model;
    }

}
