<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Ad Soyad')->searchable(),
                TextColumn::make('email')->label('E-posta')->searchable(),
                TextColumn::make('roles.name')->label('Roller')->badge(),
                TextColumn::make('created_at')->label('Oluşturulma Tarihi')->dateTime('d/m/Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Güncellenme Tarihi')->dateTime('d/m/Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make()
                    ->visible(fn (Model $record) => Auth::user()->id !== $record->id)
                    ->form([
                        Forms\Components\TextInput::make('name')
                            ->label('Ad Soyad')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('E-posta')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password')
                            ->label('Şifre')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create'),
                        Forms\Components\Select::make('roles')
                            ->label('Roller')
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->preload()
                            ->options(function () {
                                $roles = \Spatie\Permission\Models\Role::pluck('name', 'id');

                                if (! Auth::user()->hasRole('superadmin')) {
                                    $roles = $roles->filter(fn ($role) => $role !== 'superadmin');
                                }

                                return $roles;
                            })
                            ->disabled(fn (Model $record) => $record->hasRole('admin') && Auth::user()->id === $record->id),
                    ]),

                DeleteAction::make()
                    ->visible(fn (Model $record) => Auth::user()->id !== $record->id),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->action(function (EloquentCollection $records) {
                        $recordsToDelete = $records->filter(fn ($record) => $record->email !== 'mgakif@gmail.com');
                        $recordsToDelete->each->delete();
                    }),
                ]),
            ]);
    }
}
