<?php

namespace App\Filament\Support;

use App\Services\SettingsService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;

/**
 * Base page editing a set of site settings (stored in `site_settings`).
 *
 * @property-read Schema $form
 */
abstract class SettingsPage extends Page
{
    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    /**
     * Permission required to open the page.
     */
    abstract protected static function permission(): string;

    /**
     * Setting groups (config/site.php "defaults" keys) edited by the page.
     *
     * @return array<int, string>
     */
    abstract protected function groups(): array;

    /**
     * @return array<int, mixed>
     */
    abstract protected function formComponents(): array;

    public static function canAccess(): bool
    {
        return auth()->user()?->can(static::permission()) ?? false;
    }

    public function mount(): void
    {
        $settings = app(SettingsService::class);

        $this->form->fill(collect($this->groups())
            ->flatMap(fn (string $group): array => $settings->group($group))
            ->all());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components($this->formComponents())
            ->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')
                            ->label('Enregistrer')
                            ->submit('save')
                            ->keyBindings(['mod+s']),
                    ])->sticky(),
                ]),
        ]);
    }

    public function save(): void
    {
        abort_unless(static::canAccess(), 403);

        app(SettingsService::class)->set($this->form->getState());

        Notification::make()
            ->title('Paramètres enregistrés')
            ->body('Les modifications sont visibles immédiatement sur le site.')
            ->success()
            ->send();
    }
}
