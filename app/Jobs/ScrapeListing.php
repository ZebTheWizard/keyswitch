<?php

namespace App\Jobs;

use App\Enum\ScrapingStatus;
use App\Models\Scraper;
use App\Models\User;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class ScrapeListing implements ShouldQueue
{
    use Queueable;

    protected Scraper $scraper;

    protected User $user;

    /**
     * Create a new job instance.
     */
    public function __construct(int $scraperId, int $userId)
    {
        $this->scraper = Scraper::findOrFail($scraperId);
        try {
            $this->user = User::findOrFail($userId);
        } catch (Exception $err) {
            $this->failed($err);
            throw $err;
        }
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->scraper->make()
            ->scrapeListing()
            ->recordListing();

        $this->scraper->update(['status' => ScrapingStatus::READY, 'error' => null]);

        Notification::make()
            ->title('Scraping Finished')
            ->body("{$this->scraper->class} has finished.")
            ->success()
            ->sendToDatabase($this->user);
    }

    public function failed(?Throwable $exception): void
    {
        $this->scraper->update([
            'status' => ScrapingStatus::FAILED,
            'error' => implode("\n", [$exception->getMessage(), $exception->getTraceAsString()]),
        ]);

        if (isset($this->user)) {
            Notification::make()
                ->title('Scraping Failed')
                ->body("{$this->scraper->class} has failed.")
                ->danger()
                ->sendToDatabase($this->user);
        }
    }
}
