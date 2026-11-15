<?php

namespace App\Policies;

use App\Models\User;

class ContactPolicy extends ModulePolicy
{
    protected function module(): string
    {
        return 'contacts';
    }

    public function export(User $user): bool
    {
        return $this->allows($user, 'export');
    }
}
