<?php

namespace App\Filament\Resources\FormSubmissionResource\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Models\FormSubmission;

class FormSubmissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(FormSubmission::query()->with('form'))
            ->columns([
                TextColumn::make('form.name')
                    ->label('Form')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('name')
                    ->label('İsim')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('E-posta')
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Gönderim Tarihi')
                    ->dateTime('d F Y - H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('form_id')
                    ->label('Form')
                    ->relationship('form', 'name'),
            ])
            ->actions([
                ViewAction::make()
                    ->label('Görüntüle')
                    ->modalContent(fn ($record) => view('filament.custom-view-modal-content', [
                        'record' => $record,
                        'hiddenFields' => $record->form->hiddenFields ?? [],
                    ]))
                    ->modalHeading('Soru & Cevap'),

                Action::make('reply')
                    ->label('Cevapla')
                    ->url(function ($record) {
                        $email = urlencode($record->email);
                        $subject = urlencode('Form başvurunuz hakkında: '.($record->form ? $record->form->name : 'İletişim Formu'));
                        $body = urlencode("Merhaba {$record->name}, başvurunuzu inceledik.");

                        return "/admin/send-custom-mail?email={$email}&subject={$subject}&body={$body}";
                    })
                    ->icon('heroicon-s-paper-airplane')
                    ->openUrlInNewTab(),
                Action::make('toggleAnswered')
                    ->label(fn ($record) => $record->answered ? 'Cevaplandı' : 'Cevaplanmadı')
                    ->action(function ($record) {
                        $record->update(['answered' => ! $record->answered]);
                        \Filament\Notifications\Notification::make()
                            ->title($record->answered ? 'Cevaplandı olarak işaretlendi' : 'Cevaplanmadı olarak işaretlendi')
                            ->success()
                            ->send();
                    })
                    ->icon(fn ($record) => $record->answered ? 'heroicon-s-check-circle' : 'heroicon-s-x-circle')
                    ->color(fn ($record) => $record->answered ? 'success' : 'danger'),
                DeleteAction::make()
                    ->iconButton(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

