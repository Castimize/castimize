<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [];

    /**
     * Register any other events for your application.
     *
     * @return void
     */
    public function boot()
    {
        Event::listen('revisionable.*', function($model, $revisions) {
            // Do something with the revisions or the changed model.
            //dd($model, $revisions);
        });
    }
}
