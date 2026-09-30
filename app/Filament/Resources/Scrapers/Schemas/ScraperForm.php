<?php

namespace App\Filament\Resources\Scrapers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ScraperForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('class')
                    ->required(),
            ]);
    }
}
