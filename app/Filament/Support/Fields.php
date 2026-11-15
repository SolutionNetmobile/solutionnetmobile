<?php

namespace App\Filament\Support;

use App\Services\ImageOptimizer;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Reusable, pre-configured back-office fields (validation and storage rules in one place).
 */
final class Fields
{
    /**
     * Allowed image types for uploads (SVG only where explicitly enabled: logos).
     *
     * @var array<int, string>
     */
    public const array IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    public static function image(string $name, string $directory, bool $allowSvg = false, int $maxKb = 4096): FileUpload
    {
        return FileUpload::make($name)
            ->image()
            ->disk('public')
            ->directory($directory)
            ->visibility('public')
            ->acceptedFileTypes($allowSvg ? [...self::IMAGE_TYPES, 'image/svg+xml'] : self::IMAGE_TYPES)
            ->maxSize($maxKb)
            ->imagePreviewHeight('140')
            ->helperText(($allowSvg ? 'JPG, PNG, WebP ou SVG' : 'JPG, PNG ou WebP').' — '.($maxKb / 1024).' Mo maximum. Les images JPG/PNG sont converties en WebP.')
            ->saveUploadedFileUsing(
                fn (TemporaryUploadedFile $file): string => app(ImageOptimizer::class)->store($file, $directory)
            );
    }

    public static function icon(string $name = 'icon'): Select
    {
        $options = collect(config('site.icons'))
            ->mapWithKeys(fn (string $label, string $icon): array => [
                $icon => '<span style="display:inline-flex;align-items:center;gap:.5rem">'
                    .svg('lucide-'.$icon, '', ['style' => 'width:1.15rem;height:1.15rem;color:#0755C8'])->toHtml()
                    .e($label).'</span>',
            ])
            ->all();

        return Select::make($name)
            ->label('Icône')
            ->options($options)
            ->allowHtml()
            ->searchable()
            ->native(false)
            ->in(array_keys(config('site.icons')));
    }

    /**
     * Title input that fills the slug automatically on creation.
     */
    public static function titleWithSlug(string $name = 'title', string $label = 'Titre'): TextInput
    {
        return TextInput::make($name)
            ->label($label)
            ->required()
            ->maxLength(255)
            ->live(onBlur: true)
            ->afterStateUpdated(function (Set $set, ?string $state, string $operation): void {
                if ($operation === 'create') {
                    $set('slug', Str::slug((string) $state));
                }
            });
    }

    public static function slug(string $prefix): TextInput
    {
        return TextInput::make('slug')
            ->label('Slug (URL)')
            ->required()
            ->maxLength(255)
            ->alphaDash()
            ->unique(ignoreRecord: true)
            ->prefix($prefix)
            ->helperText('Lettres minuscules, chiffres et tirets uniquement.');
    }

    public static function richText(string $name, string $directory): RichEditor
    {
        return RichEditor::make($name)
            ->toolbarButtons([
                ['bold', 'italic', 'underline', 'link'],
                ['h2', 'h3'],
                ['bulletList', 'orderedList', 'blockquote'],
                ['undo', 'redo'],
            ])
            ->fileAttachmentsDisk('public')
            ->fileAttachmentsDirectory($directory)
            ->fileAttachmentsVisibility('public');
    }
}
