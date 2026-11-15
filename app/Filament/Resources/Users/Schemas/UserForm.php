<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\Role as RoleEnum;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Identité')
                    ->columns(2)
                    ->columnSpan(['lg' => 2])
                    ->schema([
                        TextInput::make('name')->label('Nom complet')->required()->maxLength(255),
                        TextInput::make('email')
                            ->label('Adresse e-mail')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('password')
                            ->label('Mot de passe')
                            ->password()
                            ->revealable()
                            ->rule(Password::min(12)->letters()->mixedCase()->numbers())
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->same('passwordConfirmation')
                            ->helperText(fn (string $operation): string => $operation === 'edit'
                                ? 'Laisser vide pour conserver le mot de passe actuel. 12 caractères minimum, majuscules, minuscules et chiffres.'
                                : '12 caractères minimum, majuscules, minuscules et chiffres.'),
                        TextInput::make('passwordConfirmation')
                            ->label('Confirmation du mot de passe')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(false),
                    ]),
                Section::make('Accès')
                    ->columnSpan(['lg' => 1])
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Compte actif')
                            ->helperText('Un compte désactivé ne peut plus se connecter.')
                            ->default(true)
                            ->disabled(fn (?User $record): bool => $record !== null && $record->is(auth()->user())),
                        Select::make('roles')
                            ->label('Rôle(s)')
                            ->multiple()
                            ->relationship(
                                'roles',
                                'name',
                                modifyQueryUsing: fn (Builder $query): Builder => auth()->user()?->isSuperAdmin()
                                    ? $query
                                    : $query->where('name', '!=', RoleEnum::SuperAdmin->value),
                            )
                            ->getOptionLabelFromRecordUsing(fn (Role $record): string => RoleEnum::labelFor($record->name))
                            ->preload()
                            ->native(false)
                            ->required()
                            ->disabled(fn (?User $record): bool => $record !== null && $record->is(auth()->user())),
                    ]),
            ]);
    }
}
