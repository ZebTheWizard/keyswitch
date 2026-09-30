<?php

namespace App\Filament\Resources\Scrapers;

use App\Filament\Resources\Scrapers\Pages\CreateScraper;
use App\Filament\Resources\Scrapers\Pages\EditScraper;
use App\Filament\Resources\Scrapers\Pages\ListScrapers;
use App\Filament\Resources\Scrapers\Schemas\ScraperForm;
use App\Filament\Resources\Scrapers\Tables\ScrapersTable;
use App\Models\Scraper;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ScraperResource extends Resource
{
    protected static ?string $model = Scraper::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CodeBracket;

    protected static ?string $recordTitleAttribute = 'class';

    public static function form(Schema $schema): Schema
    {
        return ScraperForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ScrapersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SwitchesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListScrapers::route('/'),
            'create' => CreateScraper::route('/create'),
            'edit' => EditScraper::route('/{record}/edit'),
        ];
    }
}
