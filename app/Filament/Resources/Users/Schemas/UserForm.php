<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Ad Soyad')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('E-posta')
                    ->email()
                    ->required()
                    ->maxLength(255),
                TextInput::make('password')
                    ->label('Şifre')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create'),
                Select::make('roles')
                    ->label('Roller')
                    ->multiple()
                    ->relationship('roles', 'name')
                    ->preload()
                    ->options(function () {
                        $roles = \Spatie\Permission\Models\Role::pluck('name', 'id');

                        // Eğer kullanıcı superadmin değilse, superadmin rolünü listeden çıkar
                        if (! Auth::user()->hasRole('superadmin')) {
                            $roles = $roles->filter(fn ($role) => $role !== 'superadmin');
                        }

                        return $roles;
                    }),
            ]);
    }
}
