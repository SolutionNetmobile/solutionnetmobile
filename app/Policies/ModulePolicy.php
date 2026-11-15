<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Maps the standard policy abilities onto "{module}.{action}" permissions.
 */
abstract class ModulePolicy
{
    /**
     * Permission prefix, e.g. "prestations".
     */
    abstract protected function module(): string;

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'view');
    }

    public function view(User $user, Model $model): bool
    {
        return $this->allows($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'create');
    }

    public function update(User $user, Model $model): bool
    {
        return $this->allows($user, 'update');
    }

    public function reorder(User $user): bool
    {
        return $this->allows($user, 'update');
    }

    public function delete(User $user, Model $model): bool
    {
        return $this->allows($user, 'delete');
    }

    public function deleteAny(User $user): bool
    {
        return $this->allows($user, 'delete');
    }

    public function restore(User $user, Model $model): bool
    {
        return $this->allows($user, 'delete');
    }

    public function restoreAny(User $user): bool
    {
        return $this->allows($user, 'delete');
    }

    public function forceDelete(User $user, Model $model): bool
    {
        return $this->allows($user, 'delete');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $this->allows($user, 'delete');
    }

    protected function allows(User $user, string $action): bool
    {
        return $user->is_active && $user->can("{$this->module()}.{$action}");
    }
}
