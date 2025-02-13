<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

//Artisan::command('inspire', function () {
//    $this->comment(Inspiring::quote());
//})->purpose('Display an inspiring quote')->hourly();

Schedule::command('castimize:get-currency-historical-rates')
    ->timezone('Europe/Amsterdam')
    ->at('0:01');
Schedule::command('castimize:sync-invoices-to-exact')
    ->timezone('Europe/Amsterdam')
    ->everyThreeHours();
