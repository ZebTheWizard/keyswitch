<?php

namespace App\Filament\Resources\KeySwitches\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;

class KeySwitchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('price')
                    ->numeric()
                    ->prefix('$')
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(','),
                TextInput::make('manufacturer')
                    ->required(),
                FileUpload::make('cover')
                    ->image()
                    ->disk('public')
                    ->directory('covers')
                    ->columnStart(1),
                FileUpload::make('product_images')
                    ->image()
                    ->multiple()
                    ->reorderable()
                    ->disk('public')
                    ->directory('products')
                    ->panelLayout('grid')
                    ->columnSpan(2),
            ]);
    }
}
