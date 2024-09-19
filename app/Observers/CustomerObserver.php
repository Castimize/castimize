<?php

namespace App\Observers;

use App\Models\Customer;

class CustomerObserver
{
    /**
     * Handle the User "creating" event.
     */
    public function creating(Customer $customer): void
    {
        //
    }

    /**
     * Handle the User "created" event.
     */
    public function created(Customer $customer): void
    {
        //
    }

    /**
     * Handle the User "updating" event.
     */
    public function updating(Customer $customer): void
    {
        //
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(Customer $customer): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(Customer $customer): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(Customer $customer): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(Customer $customer): void
    {
        //
    }
}
