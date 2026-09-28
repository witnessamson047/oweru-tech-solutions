<?php

namespace App\Providers;

use App\Models\Enquiry;
use App\Observers\EnquiryObserver;
use Illuminate\Support\ServiceProvider;

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
        Enquiry::observe(EnquiryObserver::class);
    }
}
