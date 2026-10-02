<?php

namespace App\Filament\Resources\KeySwitches\Pages;

use App\Filament\Resources\KeySwitches\KeySwitchResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditKeySwitch extends EditRecord
{
    protected static string $resource = KeySwitchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
