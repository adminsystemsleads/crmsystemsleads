<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Models\Team;
use App\Models\Deal;
use App\Observers\TeamObserver;
use App\Observers\DealObserver;
use App\Listeners\AssignDefaultCrmRole;
use Laravel\Jetstream\Events\TeamMemberAdded;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Team::observe(TeamObserver::class);
        Deal::observe(DealObserver::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Al agregar un usuario a una cuenta, se le asigna el rol Editor por defecto.
        Event::listen(TeamMemberAdded::class, AssignDefaultCrmRole::class);
    }
}
