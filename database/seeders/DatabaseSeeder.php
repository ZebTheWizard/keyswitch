<?php

namespace Database\Seeders;

use App\Models\Scraper;
use App\Models\User;
use App\Services\Scraping\KineticLabsScraper;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        Scraper::create([
            'class' => KineticLabsScraper::class,
        ]);

        KineticLabsScraper::make()
            ->launch()
            ->recordListing(count: config('app.scrape_count'))
            ->recordDetails();
    }
}
