<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Create the initial super administrator from ADMIN_EMAIL / ADMIN_PASSWORD.
     * When no password is configured, a random one is generated and displayed once.
     */
    public function run(): void
    {
        $email = (string) config('site.admin.email');

        if (User::query()->where('email', $email)->exists()) {
            return;
        }

        $password = (string) (config('site.admin.password') ?: Str::password(16));

        $user = User::query()->create([
            'name' => 'Florent Andreu',
            'email' => $email,
            'password' => $password,
            'is_active' => true,
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();
        $user->assignRole(Role::SuperAdmin->value);

        if (! config('site.admin.password')) {
            $this->command?->warn("Super administrateur créé : {$email} / {$password} — changez ce mot de passe dès la première connexion.");
        }
    }
}
