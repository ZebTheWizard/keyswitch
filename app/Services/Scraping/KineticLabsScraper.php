<?php

namespace App\Services\Scraping;

use App\Models\RawKeySwitch;
use Closure;
use Illuminate\Support\Arr;
use Nesk\Puphpeteer\Resources\Page;

class KineticLabsScraper extends Scraper
{
    public function launch(?Closure $method = null): static
    {
        return parent::launch(function (Page $page) use ($method) {
            $page->goto('https://kineticlabs.com/switches');
            $page->waitForSelector('a[href^="/switches/"]');

            $this->rawRecords = $page->evaluate($this->makeFunction(<<<'JS'
                const p = document.evaluate(
                    "//p[text() = 'Manufacturer']",
                    document,
                    null,
                    XPathResult.FIRST_ORDERED_NODE_TYPE, // Looks for the first matching element
                    null
                ).singleNodeValue
                return Array.from(p.parentElement.querySelectorAll(':scope > div')).map((m) => {
                    m.querySelector('div').click()
                    const switches = Array.from(document.querySelectorAll('a[href^="/switches/"]')).map((s) => {
                        const data = {
                            url: s.href,
                            raw_name: s.querySelector(':scope > div > p').innerHTML,
                            raw_price: s.querySelector(':scope > div:last-of-type div p').innerHTML,
                            raw_manufacturer: s.querySelector(':scope > div:first-of-type button').innerHTML,
                        }

                        return data;
                    })
                    m.querySelector('div').click()
                    return switches
                }).flat()
            JS));

            $this->invoke($method, $page);
        });
    }

    public function recordListing(int $count = 5): static
    {
        $switches = Arr::random($this->rawRecords, $count);
        $this->unscrapedModels = [];
        foreach ($switches as $switch) {
            $raw = RawKeySwitch::firstOrNew(['url' => data_get($switch, 'url')]);
            $raw->forceFill($switch);

            if ($raw->isDirty() || ! $raw->exists) {
                $raw->scraped_at = now();
                $raw->scraper_id = $this->id;
                $raw->save();
                $this->unscrapedModels[] = $raw;
            }
        }

        return $this;
    }

    public function recordDetails(): static
    {
        foreach ($this->unscrapedModels as $switch) {
            dump($switch);
        }

        return $this;
    }
}
