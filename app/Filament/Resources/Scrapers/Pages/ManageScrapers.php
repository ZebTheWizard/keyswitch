<?php

namespace App\Filament\Resources\Scrapers\Pages;

use App\Filament\Resources\Scrapers\ScraperResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageScrapers extends ManageRecords
{
    protected static string $resource = ScraperResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
