<?php

namespace App\Filament\Actions;

use App\Models\RawKeySwitch;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Database\Eloquent\Collection;

class ScrapeBulkAction extends BulkAction
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
            $parentModel = $livewire->getOwnerRecord();
            $this->scraper = $parentModel->class;
            $this->execute($records);
        });
    }

    /**
     * @param  Collection<RawKeySwitch>  $records
     */
    public function execute(Collection $records): void
    {
        $this->scraper::make()
            ->hydrate($records)
            ->recordDetails();

        Notification::make()
            ->title('Export Completed')
            ->success()
            ->send();
    }
}
