<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/contacts')->assertRedirect('/admin/login');
    }

    public function test_the_login_page_is_available(): void
    {
        $this->get('/admin/login')->assertOk();
    }

    public function test_users_without_back_office_role_are_forbidden(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->actingAs(User::factory()->create());

        $this->get('/admin')->assertForbidden();
    }

    public function test_deactivated_users_are_forbidden(): void
    {
        $this->actingAsRole(Role::Admin, active: false);

        $this->get('/admin')->assertForbidden();
    }

    /**
     * @return array<string, array{0: Role, 1: string, 2: int}>
     */
    public static function accessMatrix(): array
    {
        return [
            'super admin — dashboard' => [Role::SuperAdmin, '/admin', 200],
            'super admin — roles' => [Role::SuperAdmin, '/admin/roles', 200],
            'super admin — users' => [Role::SuperAdmin, '/admin/users', 200],
            'admin — dashboard' => [Role::Admin, '/admin', 200],
            'admin — contacts' => [Role::Admin, '/admin/contacts', 200],
            'admin — users' => [Role::Admin, '/admin/users', 200],
            'admin — settings' => [Role::Admin, '/admin/parametres-du-site', 200],
            'admin — agency' => [Role::Admin, '/admin/presentation-agence', 200],
            'admin — roles' => [Role::Admin, '/admin/roles', 403],
            'editor — dashboard' => [Role::Editor, '/admin', 200],
            'editor — prestations' => [Role::Editor, '/admin/prestations', 200],
            'editor — methodologies' => [Role::Editor, '/admin/methodologies', 200],
            'editor — clients' => [Role::Editor, '/admin/clients', 200],
            'editor — pages' => [Role::Editor, '/admin/pages', 200],
            'editor — agency' => [Role::Editor, '/admin/presentation-agence', 200],
            'editor — contacts' => [Role::Editor, '/admin/contacts', 403],
            'editor — users' => [Role::Editor, '/admin/users', 403],
            'editor — settings' => [Role::Editor, '/admin/parametres-du-site', 403],
            'editor — roles' => [Role::Editor, '/admin/roles', 403],
        ];
    }

    #[DataProvider('accessMatrix')]
    public function test_each_role_only_reaches_the_allowed_sections(Role $role, string $url, int $status): void
    {
        $this->actingAsRole($role);

        $this->get($url)->assertStatus($status);
    }

    public function test_the_last_login_date_is_recorded(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->withRole(Role::Admin)->create(['password' => 'Secret-Password-123']);

        $this->assertNull($user->last_login_at);

        auth()->attempt(['email' => $user->email, 'password' => 'Secret-Password-123']);

        $this->assertNotNull($user->fresh()->last_login_at);
    }
}
