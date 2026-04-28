<?php

namespace App\Providers;

use App\Models\Pipeline;
use App\Policies\PipelinePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Aquí registras tus Policies
     */
    protected $policies = [
        Pipeline::class => PipelinePolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('access-monthly-import', function ($user) {
            // Jetstream team roles
            if (class_exists(\Laravel\Jetstream\Jetstream::class) && $user->currentTeam) {
                if ($user->hasTeamRole($user->currentTeam, 'admin')) {
                    return true;
                }
            }

            // Simple flag en users (opcional)
            if (isset($user->is_admin) && $user->is_admin) {
                return true;
            }

            // (Opcional) Spatie Permission
            // if (method_exists($user, 'hasRole') && $user->hasRole('admin')) return true;

            return false;
        });
    }
}
