<?php

namespace App\Providers;

use Carbon\Carbon;
use Cmixin\BusinessDay;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use Shippo;
use Spatie\LaravelPdf\Facades\Pdf;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Shippo::setApiKey($this->app['config']['services.shippo.key']);
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });

        if ($this->app->environment('local')) {
            Mail::alwaysTo('matthijs.bon1@gmail.com');
        }

        if ($this->app->environment('staging')) {
            Mail::alwaysTo('test@castimize.com');
        }

        $this->setBusinessDays();

        // Macro to add or sub business days
        Carbon::macro('businessDays', function ($value = 1, string $type = 'add') {
            while ($value > 0) {
                $method = sprintf('%sDays', $type);
                $this->$method();
                if (!$this->isWeekend()) {
                    $type === 'add' ? ++$value : --$value;
                }
            }

            return $this;
        });

        if ($this->app->environment('production')) {
            Pdf::default()
                ->withBrowsershot(function ($browsershot) {
                    $browsershot
                        ->setIncludePath('$PATH:/usr/bin')
                        ->setChromePath('/usr/bin/chromium-browser')
                        ->setCustomTempPath(storage_path())
                        ->addChromiumArguments([
                            'headless=shell'
                        ]);
                });
        }
    }

    private function setBusinessDays()
    {
        // You can select one of our official list
        $baseList = 'us-national'; // or region such as 'us-il'

// You can add/remove days (optional):
        $additionalHolidays = [
//            'independence-day' => null, // Even if it's holiday, you can force it to null to make your business open
//            'boss-birthday'    => '09-26', // Close the office on September 26th
//            // you can also use slash if you prefer day first '26/09' (September 26th too)
//            'julian-christmas' => '= julian 12-25', // We support many calendars such as the Julian calendar
//            // We support expressions
//            'special-easter'   => '= Tuesday before easter',
//            'last-monday'      => '= last Monday of October',
//            'conditional'      => '= 02-25 if Tuesday then next Friday', // We support conditions
//            // And we support closures:
//            'very-special'     => function ($year) {
//                if ($year === 2020) {
//                    return '01-15';
//                }
//
//                return '02-15';
//            },
        ];

        // You can optionally specify some days that are worked even if on weekend
        $extraWorkDays = [];

        BusinessDay::enable(Carbon::class, $baseList, $additionalHolidays, $extraWorkDays);
        // Or if you use Laravel:
        // BusinessDay::enable('Illuminate\Support\Carbon', $baseList, $additionalHolidays);

        // And you can even enable multiple classes at once:
        BusinessDay::enable([
            'Carbon\Carbon',
            'Carbon\CarbonImmutable',
        ], $baseList, $additionalHolidays);
    }
}
