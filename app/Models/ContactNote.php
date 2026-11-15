<?php

namespace App\Models;

use App\Enums\ContactNoteType;
use Database\Factories\ContactNoteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['contact_id', 'user_id', 'type', 'body'])]
class ContactNote extends Model
{
    /** @use HasFactory<ContactNoteFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ContactNoteType::class,
        ];
    }

    /**
     * @return BelongsTo<Contact, $this>
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
