<?php

namespace App\Filament\Resources\KeySwitches;

use App\Filament\Resources\KeySwitches\Pages\CreateKeySwitch;
use App\Filament\Resources\KeySwitches\Pages\EditKeySwitch;
use App\Filament\Resources\KeySwitches\Pages\ListKeySwitches;
use App\Filament\Resources\KeySwitches\Schemas\KeySwitchForm;
use App\Filament\Resources\KeySwitches\Tables\KeySwitchesTable;
use App\Models\KeySwitch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class KeySwitchResource extends Resource
{
    protected static ?string $model = KeySwitch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return KeySwitchForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KeySwitchesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKeySwitches::route('/'),
            'create' => CreateKeySwitch::route('/create'),
            'edit' => EditKeySwitch::route('/{record}/edit'),
        ];
    }
}
