<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Role as RoleModel;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_admin_can_create_an_editor(): void
    {
        $this->actingAsRole(Role::Admin);

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Camille Éditrice',
                'email' => 'camille@example.com',
                'password' => 'Motdepasse-Solide-2026',
                'passwordConfirmation' => 'Motdepasse-Solide-2026',
                'is_active' => true,
                'roles' => [RoleModel::findByName(Role::Editor->value)->id],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $user = User::query()->where('email', 'camille@example.com')->sole();
        $this->assertTrue($user->hasRole(Role::Editor->value));
        $this->assertTrue(Hash::check('Motdepasse-Solide-2026', $user->password));
    }

    public function test_weak_passwords_are_rejected(): void
    {
        $this->actingAsRole(Role::Admin);

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Faible',
                'email' => 'faible@example.com',
                'password' => 'abc',
                'passwordConfirmation' => 'abc',
                'roles' => [RoleModel::findByName(Role::Editor->value)->id],
            ])
            ->call('create')
            ->assertHasFormErrors(['password']);
    }

    public function test_an_admin_cannot_edit_or_delete_a_super_admin(): void
    {
        $admin = $this->actingAsRole(Role::Admin);
        $superAdmin = User::factory()->withRole(Role::SuperAdmin)->create();

        $this->assertFalse($admin->can('update', $superAdmin));
        $this->assertFalse($admin->can('delete', $superAdmin));
        $this->assertFalse($admin->can('delete', $admin));
        $this->get(route('filament.admin.resources.users.edit', $superAdmin))->assertForbidden();
    }

    public function test_permissions_match_each_role(): void
    {
        $editor = $this->actingAsRole(Role::Editor);

        $this->assertTrue($editor->can('prestations.update'));
        $this->assertTrue($editor->can('agency.manage'));
        $this->assertFalse($editor->can('contacts.view'));
        $this->assertFalse($editor->can('settings.manage'));
        $this->assertFalse($editor->can('users.view'));

        $admin = User::factory()->withRole(Role::Admin)->create();
        $this->assertTrue($admin->can('contacts.export'));
        $this->assertTrue($admin->can('settings.manage'));
        $this->assertFalse($admin->can('roles.manage'));

        $superAdmin = User::factory()->withRole(Role::SuperAdmin)->create();
        $this->assertTrue($superAdmin->can('roles.manage'));
    }
}
