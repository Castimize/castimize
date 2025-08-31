<?php

namespace App\Observers;

use App\Models\Model;

class ModelObserver
{
    /**
     * Handle the Model "creating" event.
     */
    public function creating(Model $model): void {}

    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void {}

    /**
     * Handle the Model "updating" event.
     */
    public function updating(Model $model): void {}

    /**
     * Handle the Model "updated" event.
     */
    public function updated(Model $model): void {}

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void {}
}
