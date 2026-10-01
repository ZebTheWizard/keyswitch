<?php

namespace App\Filament\Actions;

use App\Enum\ScrapingStatus;
use App\Jobs\ScrapeListing;
use App\Models\Scraper;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class ScrapeListingAction extends Action
{
    protected string $scraper;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Set default properties
        $this->name('scrapeAction')
            ->label('Scrape Listing')
            ->color('info')
            ->requiresConfirmation()
            ->modalHeading('Scrape Listing')
            ->modalDescription('Are you sure you want to scrape details for the selected records?')
            ->icon(fn (?Scraper $record): string => $record?->status === ScrapingStatus::PENDING ? 'heroicon-o-arrow-path' : 'heroicon-o-arrow-down-tray')
            ->disabled(fn (Scraper $record) => $record->status == ScrapingStatus::PENDING)
            ->extraAttributes(function (?Scraper $record): array {
                if ($record?->status === ScrapingStatus::PENDING) {
                    return [
                        'class' => '[&_svg]:animate-spin',
                        'wire:poll.'.config('app.poll_rate') => '$refresh',
                    ];
                }

                return [];
            });

        $this->action(function (EditRecord $livewire) {
            /** @var Scraper */
            $model = $livewire->getRecord();
            $model->update(['status' => 'pending']);

            ScrapeListing::dispatch(
                scraperId: $model->id,
                userId: auth()->user()->id,
            );

            Notification::make()
                ->title('Scraping Started')
                ->body('The process is running in the background.')
                ->info()
                ->send();

        });
    }
}
