<?php

namespace App\Support;

use App\Enums\Role;

/**
 * Single source of truth for back-office permissions and their assignment to roles.
 */
final class Permissions
{
    /**
     * CRUD modules and their French label.
     *
     * @var array<string, string>
     */
    public const array MODULES = [
        'prestations' => 'Prestations',
        'methodologies' => 'Méthodologie',
        'clients' => 'Clients & références',
        'pages' => 'Pages',
        'contacts' => 'Prospects',
        'users' => 'Utilisateurs',
    ];

    /**
     * @var array<string, string>
     */
    public const array ACTIONS = [
        'view' => 'Consulter',
        'create' => 'Créer',
        'update' => 'Modifier',
        'delete' => 'Supprimer',
    ];

    /**
     * Permissions that are not tied to a CRUD module.
     *
     * @var array<string, string>
     */
    public const array EXTRA = [
        'contacts.export' => 'Exporter les prospects',
        'agency.manage' => 'Gérer la présentation de l’agence',
        'settings.manage' => 'Gérer les paramètres du site',
        'roles.manage' => 'Gérer les rôles et permissions',
    ];

    /**
     * Every permission with its French label.
     *
     * @return array<string, string>
     */
    public static function all(): array
    {
        $permissions = [];

        foreach (self::MODULES as $module => $moduleLabel) {
            foreach (self::ACTIONS as $action => $actionLabel) {
                $permissions["{$module}.{$action}"] = "{$moduleLabel} : {$actionLabel}";
            }
        }

        return $permissions + self::EXTRA;
    }

    /**
     * Default permissions granted to each role (the super admin bypasses all checks).
     *
     * @return array<int, string>
     */
    public static function forRole(Role $role): array
    {
        $contentModules = ['prestations', 'methodologies', 'clients', 'pages'];

        return match ($role) {
            Role::SuperAdmin => array_keys(self::all()),
            Role::Admin => array_values(array_diff(array_keys(self::all()), ['roles.manage'])),
            Role::Editor => [
                ...self::moduleActions($contentModules),
                'agency.manage',
            ],
        };
    }

    public static function label(string $permission): string
    {
        return self::all()[$permission] ?? $permission;
    }

    /**
     * @param  array<int, string>  $modules
     * @return array<int, string>
     */
    private static function moduleActions(array $modules): array
    {
        $permissions = [];

        foreach ($modules as $module) {
            foreach (array_keys(self::ACTIONS) as $action) {
                $permissions[] = "{$module}.{$action}";
            }
        }

        return $permissions;
    }
}
