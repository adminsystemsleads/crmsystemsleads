<?php

namespace App\Support;

/**
 * Catálogo único de permisos del CRM.
 *
 * Cada permiso tiene un key estable (NUNCA renombrar — solo agregar). El key se
 * persiste en crm_roles.permissions (JSON) y se consulta vía
 * $user->hasCrmPermission('contacts.delete').
 *
 * Para AGREGAR un permiso nuevo: añade la entrada bajo el grupo correspondiente.
 * Para AGREGAR un grupo nuevo: añade una entrada en groups() con label + permissions.
 */
class CrmPermissions
{
    public static function groups(): array
    {
        return [
            'pipelines' => [
                'label' => 'Embudos',
                'icon'  => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                'permissions' => [
                    'pipelines.view_all'  => 'Ver todos los embudos',
                    'pipelines.create'    => 'Crear embudos',
                    'pipelines.edit'      => 'Editar embudos (nombre, etapas)',
                    'pipelines.delete'    => 'Eliminar embudos',
                    'pipelines.configure' => 'Configurar permisos de un embudo',
                ],
            ],
            'deals' => [
                'label' => 'Negociaciones',
                'icon'  => 'M3 3h7v7H3zm11 0h7v7h-7zM3 14h7v7H3zm11 3.5a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z',
                'permissions' => [
                    'deals.view_all' => 'Ver todas las negociaciones',
                    'deals.view_own' => 'Ver solo sus negociaciones asignadas',
                    'deals.create'   => 'Crear negociaciones',
                    'deals.edit'     => 'Editar negociaciones',
                    'deals.delete'   => 'Eliminar negociaciones',
                    'deals.export'   => 'Exportar negociaciones',
                    'deals.import'   => 'Importar negociaciones',
                ],
            ],
            'contacts' => [
                'label' => 'Contactos',
                'icon'  => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                'permissions' => [
                    'contacts.view_all' => 'Ver todos los contactos',
                    'contacts.view_own' => 'Ver solo sus contactos asignados',
                    'contacts.create'   => 'Crear contactos',
                    'contacts.edit'     => 'Editar contactos',
                    'contacts.delete'   => 'Eliminar contactos',
                    'contacts.export'   => 'Exportar contactos',
                    'contacts.import'   => 'Importar contactos',
                ],
            ],
            'products' => [
                'label' => 'Productos',
                'icon'  => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10',
                'permissions' => [
                    'products.view'   => 'Ver catálogo de productos',
                    'products.create' => 'Crear productos',
                    'products.edit'   => 'Editar productos',
                    'products.delete' => 'Eliminar productos',
                ],
            ],
            'invoices' => [
                'label' => 'Facturas',
                'icon'  => 'M9 12h6m-6 4h6m2 4H7a2 2 0 01-2-2V6a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z',
                'permissions' => [
                    'invoices.view'      => 'Ver facturas y boletas',
                    'invoices.create'    => 'Crear facturas y boletas',
                    'invoices.edit'      => 'Editar facturas',
                    'invoices.delete'    => 'Eliminar facturas',
                    'invoices.export'    => 'Exportar facturas',
                    'invoices.configure' => 'Configurar facturación (SUNAT, series)',
                ],
            ],
            'whatsapp' => [
                'label' => 'WhatsApp',
                'icon'  => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
                'permissions' => [
                    'whatsapp.view_all' => 'Ver todas las conversaciones',
                    'whatsapp.view_own' => 'Ver solo sus conversaciones asignadas',
                    'whatsapp.send'     => 'Enviar mensajes',
                    'whatsapp.delete'   => 'Eliminar mensajes',
                    'whatsapp.use_ai'   => 'Usar asistente IA',
                    'whatsapp.manage_accounts' => 'Administrar cuentas de WhatsApp',
                ],
            ],
            'custom_fields' => [
                'label' => 'Campos personalizados',
                'icon'  => 'M11 5h2m-1 0v14m-7-7h14',
                'permissions' => [
                    'custom_fields.view'   => 'Ver campos personalizados',
                    'custom_fields.manage' => 'Crear, editar y eliminar campos personalizados',
                ],
            ],
            'forms' => [
                'label' => 'Formularios',
                'icon'  => 'M9 12h6m-6 4h6m-6-8h6M5 4h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V5a1 1 0 011-1z',
                'permissions' => [
                    'forms.access' => 'Acceso a formularios (crear y gestionar)',
                ],
            ],
            'reports' => [
                'label' => 'Reportes',
                'icon'  => 'M9 12l2 2 4-4m5 2a9 9 0 11-18 0 9 9 0 0118 0z',
                'permissions' => [
                    'reports.view'   => 'Ver reportes',
                    'reports.export' => 'Exportar reportes',
                ],
            ],
            'admin' => [
                'label' => 'Administración del CRM',
                'icon'  => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
                'permissions' => [
                    'admin.manage_crm_roles' => 'Configurar Roles y Permisos de CRM',
                    'admin.manage_team'      => 'Gestionar miembros del equipo',
                    'admin.manage_profiles'  => 'Gestionar Perfiles del equipo',
                    'admin.manage_modules'   => 'Configurar Módulos activos',
                ],
            ],
        ];
    }

    /**
     * Lista plana de todos los keys permitidos. Útil para validación.
     */
    public static function allKeys(): array
    {
        $keys = [];
        foreach (self::groups() as $group) {
            $keys = array_merge($keys, array_keys($group['permissions']));
        }
        return $keys;
    }

    /**
     * Permisos por defecto del rol "Editor" — read, create, update.
     * NO incluye: delete, configure, manage_accounts, manage_team, manage_crm_roles.
     */
    public static function editorDefaultKeys(): array
    {
        return [
            // Embudos: ver, crear, editar (no eliminar, no configurar permisos)
            'pipelines.view_all',
            'pipelines.create',
            'pipelines.edit',

            // Negociaciones: ver, crear, editar, importar/exportar (no eliminar)
            'deals.view_all',
            'deals.view_own',
            'deals.create',
            'deals.edit',
            'deals.export',
            'deals.import',

            // Contactos: ver, crear, editar, importar/exportar (no eliminar)
            'contacts.view_all',
            'contacts.view_own',
            'contacts.create',
            'contacts.edit',
            'contacts.export',
            'contacts.import',

            // Productos: ver, crear, editar (no eliminar)
            'products.view',
            'products.create',
            'products.edit',

            // Facturas: ver, crear, editar, exportar (no eliminar ni configurar SUNAT)
            'invoices.view',
            'invoices.create',
            'invoices.edit',
            'invoices.export',

            // WhatsApp: ver y enviar mensajes, usar IA (no eliminar ni gestionar cuentas)
            'whatsapp.view_all',
            'whatsapp.view_own',
            'whatsapp.send',
            'whatsapp.use_ai',

            // Campos personalizados: solo ver (manage incluye eliminar)
            'custom_fields.view',

            // Reportes: ver y exportar
            'reports.view',
            'reports.export',
        ];
    }

    /**
     * Etiqueta legible de un permiso por su key.
     */
    public static function label(string $key): ?string
    {
        foreach (self::groups() as $group) {
            if (isset($group['permissions'][$key])) {
                return $group['permissions'][$key];
            }
        }
        return null;
    }
}
