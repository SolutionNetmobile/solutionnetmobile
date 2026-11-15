<?php

namespace App\Models;

use App\Enums\Budget;
use App\Enums\ContactNoteType;
use App\Enums\ContactStatus;
use App\Enums\ProjectType;
use Database\Factories\ContactFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['first_name', 'last_name', 'company', 'email', 'phone', 'project_type', 'budget', 'message', 'privacy_accepted_at', 'status', 'archived_at'])]
class Contact extends Model
{
    /** @use HasFactory<ContactFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'new',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ContactStatus::class,
            'project_type' => ProjectType::class,
            'budget' => Budget::class,
            'privacy_accepted_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updated(function (Contact $contact): void {
            if (! $contact->wasChanged('status')) {
                return;
            }

            $previous = ContactStatus::tryFrom((string) $contact->getRawOriginal('status'));

            $contact->notes()->create([
                'user_id' => auth()->id(),
                'type' => ContactNoteType::StatusChange,
                'body' => sprintf(
                    'Statut modifié : %s → %s',
                    $previous?->getLabel() ?? '—',
                    $contact->status->getLabel(),
                ),
            ]);
        });
    }

    /**
     * Internal notes and exchange history. Never exposed on the public site.
     *
     * @return HasMany<ContactNote, $this>
     */
    public function notes(): HasMany
    {
        return $this->hasMany(ContactNote::class)->latest();
    }

    /**
     * @return Attribute<string, never>
     */
    protected function fullName(): Attribute
    {
        return Attribute::get(fn (): string => trim("{$this->first_name} {$this->last_name}"));
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function notArchived(Builder $query): void
    {
        $query->whereNull('archived_at');
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function archived(Builder $query): void
    {
        $query->whereNotNull('archived_at');
    }
}
