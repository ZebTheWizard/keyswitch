<?php

namespace App\Filament\Resources\Scrapers\Schemas;

use App\Enum\ScrapingStatus;
use App\Models\Scraper;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;

class ScraperForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('class')
                    ->required(),
                TextEntry::make('status')
                    ->badge()
                    ->size(TextSize::Large)
                    ->color(fn (ScrapingStatus $state): string => $state->getColor())
                    ->extraAttributes(function (): array {
                        return [
                            'wire:poll.'.config('app.poll_rate') => '$refresh',
                        ];
                    }),

                TextEntry::make('error')
                    ->columnSpan(2)
                    ->visible(fn (Scraper $record) => $record->status === ScrapingStatus::FAILED)
                    ->fontFamily('mono')
                    ->extraAttributes([
                        'class' => 'max-h-48 overflow-y-auto rounded-lg border border-gray-200 p-3 bg-gray-50 dark:border-gray-800 dark:bg-gray-900',
                    ]),
            ]);
    }
}
