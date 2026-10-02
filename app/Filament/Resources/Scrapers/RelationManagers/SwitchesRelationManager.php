<?php

namespace App\Filament\Resources\Scrapers\RelationManagers;

use App\Enum\ScrapingStatus;
use App\Filament\Actions\ScrapeDetailsBulkAction;
use App\Models\Scraper;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class SwitchesRelationManager extends RelationManager
{
    protected static string $relationship = 'switches';

    protected function isTablePollingEnabled(): bool
    {
        /** @var Scraper */
        $model = $this->getOwnerRecord();

        return $model->status === ScrapingStatus::PENDING;
    }

    protected function getTablePollingInterval(): ?string
    {
        return config('app.poll_rate');
    }

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
            ->deselectAllRecordsWhenFiltered(false)
            ->columns([
                ImageColumn::make('raw_cover'),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('review_reason')
                    ->html()
                    ->formatStateUsing(fn (?string $state) => $state ? new HtmlString(nl2br(e($state))) : null)
                    ->searchable(),
                TextColumn::make('url')
                    ->searchable(),
                TextColumn::make('raw_name')
                    ->searchable(),
                TextColumn::make('raw_price')
                    ->searchable()
                    ->money('usd'),
                TextColumn::make('raw_manufacturer')
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
