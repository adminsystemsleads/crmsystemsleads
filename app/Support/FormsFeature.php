<?php

namespace App\Support;

use App\Models\User;

/**
 * Candado del módulo "Formularios" mientras está en desarrollo.
 *
 * Por ahora solo los correos en DEV_EMAILS pueden ver y usar el módulo
 * (menú lateral, pantalla de Módulos y rutas). Para el resto aparece como
 * "Próximamente" y queda oculto/bloqueado.
 *
 * Cuando el módulo esté listo para todos los clientes, cambia
 * GLOBALLY_ENABLED a true: a partir de ahí el acceso lo gobiernan el módulo
 * activo del team y el permiso de rol "acceso a formularios".
 */
class FormsFeature
{
    /** Correos con acceso anticipado durante el desarrollo. */
    private const DEV_EMAILS = [
        'admin@systemsleads.com',
    ];

    /** Cambiar a true para liberar el módulo a todos los clientes. */
    private const GLOBALLY_ENABLED = false;

    public static function enabledGlobally(): bool
    {
        return self::GLOBALLY_ENABLED;
    }

    public static function accessibleBy(?User $user): bool
    {
        if (self::GLOBALLY_ENABLED) return true;
        if (!$user) return false;

        return in_array(strtolower((string) $user->email), self::DEV_EMAILS, true);
    }
}
