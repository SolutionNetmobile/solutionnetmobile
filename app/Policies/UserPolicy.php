<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserPolicy extends ModulePolicy
{
    protected function module(): string
    {
        return 'users';
    }

    /**
     * Only a super administrator may modify another super administrator.
     *
     * @param  User  $model
     */
    public function update(User $user, Model $model): bool
    {
        if ($model->isSuperAdmin() && ! $user->isSuperAdmin()) {
            return false;
        }

        return parent::update($user, $model);
    }

    /**
     * Nobody can delete their own account, and only a super administrator may delete another one.
     *
     * @param  User  $model
     */
    public function delete(User $user, Model $model): bool
    {
        if ($user->is($model) || ($model->isSuperAdmin() && ! $user->isSuperAdmin())) {
            return false;
        }

        return parent::delete($user, $model);
    }
}
