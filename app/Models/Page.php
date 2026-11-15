<?php

namespace App\Models;

use App\Enums\PageStatus;
use App\Models\Concerns\FlushesPublicContentCache;
use App\Models\Concerns\HasPublicImageUrl;
use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['title', 'slug', 'content', 'image', 'meta_title', 'meta_description', 'status', 'published_at'])]
class Page extends Model
{
    /** Slug of the legal notice page, linked from the footer. */
    public const string LEGAL_NOTICE = 'mentions-legales';

    /** Slug of the privacy policy page, linked from the footer and the contact form. */
    public const string PRIVACY_POLICY = 'politique-de-confidentialite';

    /** @use HasFactory<PageFactory> */
    use FlushesPublicContentCache, HasFactory, HasPublicImageUrl, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PageStatus::class,
            'published_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Published pages whose publication date has been reached.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('status', PageStatus::Published)
            ->where(fn (Builder $query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->publicUrl($this->image));
    }
}
