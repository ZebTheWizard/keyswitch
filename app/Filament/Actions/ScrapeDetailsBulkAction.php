<?php

namespace App\Filament\Actions;

use App\Models\Scraper;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ScrapeDetailsBulkAction extends BulkAction
{
    protected string $scraper;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Set default properties
        $this->name('scrapeAction')
            ->label('Scrape Details')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Scrape Details')
            ->modalDescription('Are you sure you want to scrape details for the selected records?');

        $this->action(function (Collection $records, RelationManager $livewire) {
            /** @var Scraper */
            $parentModel = $livewire->getOwnerRecord();
            $this->scraper = $parentModel->class;
            $this->execute($records);
        });
    }

    /**
     * @param  Collection<int, Model>  $records
     */
    public function execute(Collection $records): void
    {
        foreach ($records as $record) {
            $this->scraper::make()
                ->scrapeDetails(data_get($record, 'url'))
                ->recordDetails();
        }

        Notification::make()
            ->title('Scrape Completed')
            ->success()
            ->send();
    }
}
