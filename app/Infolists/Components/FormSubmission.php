<?php

namespace App\Infolists\Components;

use Filament\Infolists\Components\Component;

class FormSubmission extends Component
{
    protected string $view = 'infolists.components.form-submission';

    public static function make(): static
    {
        return app(static::class);
    }
}
