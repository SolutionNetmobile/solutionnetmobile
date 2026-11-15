<?php

namespace Tests\Unit;

use App\Enums\Role;
use App\Support\Html;
use App\Support\Permissions;
use PHPUnit\Framework\TestCase;

class PermissionsTest extends TestCase
{
    public function test_every_module_has_the_four_crud_permissions(): void
    {
        $all = Permissions::all();

        foreach (array_keys(Permissions::MODULES) as $module) {
            foreach (['view', 'create', 'update', 'delete'] as $action) {
                $this->assertArrayHasKey("{$module}.{$action}", $all);
            }
        }

        $this->assertCount(28, $all);
    }

    public function test_roles_receive_the_expected_permissions(): void
    {
        $editor = Permissions::forRole(Role::Editor);
        $admin = Permissions::forRole(Role::Admin);

        $this->assertContains('prestations.update', $editor);
        $this->assertNotContains('contacts.view', $editor);
        $this->assertNotContains('settings.manage', $editor);
        $this->assertContains('contacts.export', $admin);
        $this->assertNotContains('roles.manage', $admin);
        $this->assertSame(array_keys(Permissions::all()), Permissions::forRole(Role::SuperAdmin));
    }

    public function test_rich_text_is_sanitized(): void
    {
        $clean = Html::clean('<p onclick="x()">Texte <a href="javascript:alert(1)">lien</a></p><script>alert(1)</script>');

        $this->assertStringNotContainsString('script', $clean);
        $this->assertStringNotContainsString('onclick', $clean);
        $this->assertStringNotContainsString('javascript:', $clean);
        $this->assertStringContainsString('Texte', $clean);
    }
}
