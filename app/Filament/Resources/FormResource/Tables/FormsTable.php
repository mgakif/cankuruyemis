<?php

namespace App\Filament\Resources\FormResource\Tables;

use App\Models\Form as FormModel;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FormsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),

                TextColumn::make('slug')
                    ->searchable(),
                
                TextColumn::make('trello_id'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                Action::make('clone')
                    ->label('Klonla')
                    ->action(function (FormModel $record) {
                        $clonedForm = $record->replicate();
                        $clonedForm->name .= ' (Klon)';
                        $clonedForm->slug .= '_kopya';
                        $clonedForm->save();

                        foreach ($record->fields as $field) {
                            $clonedField = $field->replicate();
                            $clonedField->form_id = $clonedForm->id;
                            $clonedField->save();
                        }
                    })
                    ->requiresConfirmation()
                    ->icon('heroicon-o-document-duplicate'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

