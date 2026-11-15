<?php

namespace Tests;

use App\Enums\Role;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Create the roles/permissions and authenticate a back-office user with the given role.
     */
    protected function actingAsRole(Role $role, bool $active = true): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->state(['is_active' => $active])->withRole($role)->create();

        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        return $user;
    }
}
