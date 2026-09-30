<?php

namespace App\Services\Scraping;

use App\Models\RawKeySwitch;
use Closure;
use Illuminate\Support\Arr;
use Nesk\Puphpeteer\Resources\Page;
use Nesk\Rialto\Data\JsFunction;

class KineticLabsScraper extends Scraper
{
    /**
     * @var array<mixed>
     */
    protected array $switches;

    /**
     * @var array<mixed>
     */
    protected array $zeroSpecsSwitches;

    public function launch(?Closure $method = null): static
    {
        return parent::launch(function (Page $page) use ($method) {
            $page->goto('https://kineticlabs.com/switches');
            $page->waitForSelector('a[href^="/switches/"]');

            $this->switches = $page->evaluate((new JsFunction)->createWithBody(<<<'JS'
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
        $switches = Arr::random($this->switches, $count);
        $this->zeroSpecsSwitches = [];
        foreach ($switches as $switch) {
            $raw = RawKeySwitch::firstOrNew(['url' => data_get($switch, 'url')]);
            $raw->forceFill($switch);

            if ($raw->isDirty() || ! $raw->exists) {
                $raw->scraped_at = now();
                $raw->save();
                $this->zeroSpecsSwitches[] = $switch;
            }
        }

        return $this;
    }

    public function recordSpecs(): static
    {
        foreach ($this->zeroSpecsSwitches as $switch) {
            dump($switch);
        }

        return $this;
    }
}
