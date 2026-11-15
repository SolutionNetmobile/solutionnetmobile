<?php

namespace Database\Seeders;

use App\Enums\Role as RoleEnum;
use App\Support\Permissions;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Create the permissions and the three back-office roles (idempotent).
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (array_keys(Permissions::all()) as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach (RoleEnum::cases() as $roleEnum) {
            Role::findOrCreate($roleEnum->value, 'web')
                ->syncPermissions(Permissions::forRole($roleEnum));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
