<?php

namespace App\Filament\Resources\Scrapers\RelationManagers;

use App\Filament\Actions\ScrapeDetailsBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SwitchesRelationManager extends RelationManager
{
    protected static string $relationship = 'switches';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('url')
                    ->url()
                    ->required(),
                TextInput::make('raw_name')
                    ->required(),
                TextInput::make('raw_price')
                    ->required(),
                TextInput::make('raw_manufacturer')
                    ->required(),
                TextInput::make('raw_cover'),
                Textarea::make('raw_data')
                    ->columnSpanFull(),
                DateTimePicker::make('scraped_at')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('url')
            ->columns([
                TextColumn::make('url')
                    ->searchable(),
                TextColumn::make('raw_name')
                    ->searchable(),
                TextColumn::make('raw_price')
                    ->searchable(),
                TextColumn::make('raw_manufacturer')
                    ->searchable(),
                TextColumn::make('raw_cover')
                    ->searchable(),
                TextColumn::make('scraped_at')
                    ->dateTime()
                    ->sortable(),
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
            ->headerActions([
                // CreateAction::make(),
                // AssociateAction::make(),
            ])
            ->recordActions([
                // EditAction::make(),
                // DissociateAction::make(),
                // DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ScrapeDetailsBulkAction::make(),
                    // DissociateBulkAction::make(),
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
