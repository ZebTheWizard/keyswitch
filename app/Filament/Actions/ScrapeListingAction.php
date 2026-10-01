<?php

namespace App\Filament\Actions;

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
            ->icon(fn (?Scraper $record): string => $record?->status === 'pending' ? 'heroicon-o-arrow-path' : 'heroicon-o-arrow-down-tray')
            ->disabled(fn (Scraper $record) => $record->status == 'pending')
            ->extraAttributes(function (?Scraper $record): array {
                if ($record?->status === 'pending') {
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
            $this->scraper = $model->class;
            $this->execute();
        });
    }

    public function execute(): void
    {
        // $this->scraper::make()
        //     ->scrapeListing()
        //     ->recordListing();

        Notification::make()
            ->title('Scraping Started')
            ->body('The process is running in the background.')
            ->info()
            ->send();
    }
}
