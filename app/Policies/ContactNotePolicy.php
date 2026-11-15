<?php

namespace App\Policies;

use App\Enums\ContactNoteType;
use App\Models\ContactNote;
use App\Models\User;

/**
 * Internal notes follow the prospect permissions. Automatic status-change entries are immutable.
 */
class ContactNotePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active && $user->can('contacts.view');
    }

    public function view(User $user, ContactNote $note): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->is_active && $user->can('contacts.update');
    }

    public function update(User $user, ContactNote $note): bool
    {
        return $note->type !== ContactNoteType::StatusChange && $this->create($user);
    }

    public function delete(User $user, ContactNote $note): bool
    {
        return $note->type !== ContactNoteType::StatusChange && $user->is_active && $user->can('contacts.delete');
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
