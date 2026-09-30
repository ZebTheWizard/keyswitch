<?php

namespace App\Filament\Resources\Scrapers\Pages;

use App\Filament\Resources\Scrapers\ScraperResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditScraper extends EditRecord
{
    protected static string $resource = ScraperResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
