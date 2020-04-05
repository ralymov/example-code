<?php

namespace App\Listeners;


use App\Events\Common\ModelCreated;
use App\Events\Common\ModelDeleted;
use App\Events\Common\ModelSaved;
use App\Events\Common\ModelUpdated;
use Log;

class ModelEventSubscriber {


    public function onModelCreated($event) {
        $event->model->logModelCreated($event);
    }

    public function onModelSaved($event) {
        $event->model->logModelSaved($event);
    }

    public function onModelUpdated($event) {
        $event->model->logModelUpdated($event);
    }

    public function onModelDeleted($event) {
        $event->model->logModelDeleted($event);
    }


    public function subscribe($events) {
        $events->listen(
            ModelSaved::class,
            'App\Listeners\ModelEventSubscriber@onModelSaved'
        );
        $events->listen(
            ModelCreated::class,
            'App\Listeners\ModelEventSubscriber@onModelCreated'
        );
        $events->listen(
            ModelUpdated::class,
            'App\Listeners\ModelEventSubscriber@onModelUpdated'
        );
        $events->listen(
            ModelDeleted::class,
            'App\Listeners\ModelEventSubscriber@onModelDeleted'
        );
    }
}
