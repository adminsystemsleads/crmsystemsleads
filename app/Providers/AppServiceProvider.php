<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Team;
use App\Observers\TeamObserver;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Team::observe(TeamObserver::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
