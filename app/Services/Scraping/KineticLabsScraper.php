<?php

namespace App\Services\Scraping;

use App\Models\RawKeySwitch;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Nesk\Puphpeteer\Resources\Page;
use Nesk\Rialto\Data\JsFunction;

class KineticLabsScraper extends Scraper
{
    /**
     * @var array<mixed>
     */
    protected array $details;

    public function scrapeListing(): static
    {
        return parent::launch(function (Page $page) {
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
                            raw_name: s.querySelector(':scope > div > p')?.innerHTML,
                            raw_price: parseFloat(s.querySelector(':scope > div:last-of-type div p')?.innerHTML?.replace(/[^0-9.-]+/g, "")),
                            raw_manufacturer: s.querySelector(':scope > div:first-of-type button')?.innerHTML,
                            raw_cover: s.querySelector(':scope > div > img')?.src
                        }

                        return data;
                    })
                    m.querySelector('div').click()
                    return switches
                }).flat()
            JS));
        });
    }

    public function recordListing(?int $count = null): static
    {
        $switches = $count ? Arr::random($this->rawRecords, $count) : $this->rawRecords;

        foreach ($switches as $switch) {
            $raw = RawKeySwitch::firstOrNew(['url' => data_get($switch, 'url')]);
            $raw->forceFill($switch);

            $this->validateRawKeySwitch($raw);

            if ($raw->isDirty() || ! $raw->exists) {
                $raw->scraped_at = now();
                $raw->scraper_id = $this->id;
                $raw->save();
            }
        }

        return $this;
    }

    public function scrapeDetails(string $url): static
    {
        return parent::launch(function (Page $page) use ($url) {

            // @phpstan-ignore method.staticCall
            $page->on('response', JsFunction::createWithParameters(['response'])
                ->body(<<<'JS'
                    const url = response.url();
                    if (url.includes('page?handle')) {
                        response.json().then(data => {
                            // hack to communicate between node and php see RialtoInterceptLogger
                            console.log(btoa(JSON.stringify(data)));
                        }).catch(() => {});
                    }
                JS)
            );

            $page->goto($url, [
                'waitUntil' => 'networkidle2',
            ]);

            $payload = $this->jsonStack[0];

            $pageJson = [
                'audio' => collect((array) data_get($payload, 'audioFiles', []))
                    ->pluck('audioUrl')
                    ->toArray(),
                'compatibility' => data_get($payload, 'compatibilityNote'),
                ...(collect((array) data_get($payload, 'specs', []))
                    ->mapWithKeys(fn ($o) => [(string) data_get($o, 'title') => data_get($o, 'value')])),
            ];

            $page->waitForSelector("xpath///button[div[contains(text(), 'Tech Specs')]]");

            $details = $page->evaluate($this->makeFunction(<<<'JS'
                const xpath = (path) => document.evaluate(path, document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue
                const specsButton = xpath("//button[div[contains(text(), 'Tech Specs')]]");

                specsButton.click();

                const details = Array.from(specsButton.nextSibling.querySelectorAll('p')).slice(0, -1).reduce((acc, current, index, array) => {
                  if (index % 2 === 0) {
                    acc[current.innerHTML.replace(/[\s\-]+/g, '_').toLowerCase()] = array[index + 1].innerHTML;
                  }
                  return acc;
                }, {})

                const compButton = xpath("//button[div[contains(text(), 'Compatibility')]]")
                compButton.click()
                details['compatibility'] = compButton.nextSibling.querySelector('div').innerHTML

                const highlightsButton = xpath("//button[div[contains(text(), 'Highlights')]]")
                highlightsButton.click()
                const highlights = Array.from(highlightsButton.nextSibling.querySelectorAll('p'));

                details['type'] = highlights[0]?.innerHTML
                details['pre_lubed'] = highlights[2]?.innerHTML === 'Pre Lubed'
                details['quality'] = highlights[4]?.innerHTML
                details['spring'] = highlights[6]?.innerHTML.split(' ')[0]

                details['product_images'] = Array.from(document.querySelectorAll("button[aria-label*='zoom'] img[fetchpriority='high']")).map(n => n.src)

                return details;
            JS));

            data_set($details, 'url', $url);
            $this->details = [
                ...$details,
                ...$pageJson,
            ];
        });
    }

    public function recordDetails(): static
    {
        $raw = RawKeySwitch::firstOrNew(['url' => data_get($this->details, 'url')]);
        data_forget($this->details, 'url');
        $raw->forceFill([
            'raw_data' => $this->details,
        ]);

        $this->validateRawKeySwitch($raw);

        if ($raw->isDirty() || ! $raw->exists) {
            $raw->scraped_at = now();
            $raw->scraper_id = $this->id;
            $raw->save();
        }

        return $this;
    }

    public function validateRawKeySwitch(RawKeySwitch $raw): void
    {
        $validator = Validator::make($raw->attributesToArray(), [
            'raw_name' => ['required'],
            'raw_price' => ['required', 'min:0', 'numeric:strict'],
            'raw_manufacturer' => ['required'],
            'raw_cover' => ['required', 'active_url'],
            'raw_data' => ['array'],
        ], [
            'raw_data' => 'Missing product specs',
        ]);

        $this->validateModel(model: $raw, validator: $validator);
    }
}
