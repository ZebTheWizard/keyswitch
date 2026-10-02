<?php

namespace App\Filament\Actions;

use App\Filament\Resources\KeySwitches\KeySwitchResource;
use App\Models\RawKeySwitch;
use App\Models\Scraper;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;

class EditSwitchFromRawAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        // 1. Set default properties
        $this->name('editSwitchFromRaw')
            ->label('Edit')
            ->color('primary')
            ->icon('heroicon-o-pencil-square');

        $this->action(function (RawKeySwitch $record, RelationManager $livewire) {
            /** @var Scraper */
            $scraper = $livewire->getOwnerRecord();
            $switch = $record->keySwitch;
            if (! $switch) {
                $switch = $scraper->class::make()
                    ->createSwitchFromRaw($record);
            }

            return redirect(KeySwitchResource::getUrl('edit', ['record' => $switch]));
        });
    }
}
