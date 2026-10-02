<?php

namespace App\Filament\Resources\KeySwitches\Pages;

use App\Filament\Resources\KeySwitches\KeySwitchResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKeySwitches extends ListRecords
{
    protected static string $resource = KeySwitchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
