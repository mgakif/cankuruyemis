<?php

namespace App\Filament\Resources\FormSubmissionResource\Schemas;

use Filament\Schemas\Schema;

class FormSubmissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // KeyValue::make('data')
                //     ->label('Form Verisi')
                //     ->disabled()
                //     ->columnSpan('full'),
            ]);
    }
}

