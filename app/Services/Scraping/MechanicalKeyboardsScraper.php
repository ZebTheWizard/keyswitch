<?php

namespace App\Services\Scraping;

use App\Models\KeySwitch;
use App\Models\RawKeySwitch;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Nesk\Puphpeteer\Resources\Page;

class MechanicalKeyboardsScraper extends Scraper
{
    /**
     * @var array<mixed>
     */
    protected array $collections = [
        'https://mechanicalkeyboards.com/collections/linear-switches',
        'https://mechanicalkeyboards.com/collections/tactile-switches',
        'https://mechanicalkeyboards.com/collections/clicky-switches',
        'https://mechanicalkeyboards.com/collections/he-magnetic-switches',
        'https://mechanicalkeyboards.com/collections/silent-switches',
    ];

    public function scrapeListing(): static
    {
        return parent::launch(function (Page $page) {
            foreach ($this->collections as $collectionUrl) {
                $page->goto($collectionUrl);
                $page->waitForSelector('#ProductGridContainer');

                for ($i = 0; $i < 100; $i++) {
                    $rawRecords = $page->evaluate($this->makeFunction(<<<'JS'
                        return Array.from(document.querySelectorAll('.collection-product-card')).map(c => ({
                            url: c.querySelector('a')?.href,
                            raw_name: c.querySelector('.card__title a')?.innerHTML,
                            raw_price: c.querySelector('.price-item--regular')?.innerHTML?.replace(/[^0-9.-]+/g, ""),
                            raw_manufacturer: c.querySelector('.card__vendor')?.innerHTML?.trim(),
                            raw_cover: c.querySelector('.media--first')?.src
                        }));
                    JS));

                    $this->rawRecords = array_merge($this->rawRecords, $rawRecords);

                    $nextButton = $page->querySelector('.pagination__item--next');

                    if (empty($nextButton)) {
                        break;
                    }

                    // @phpstan-ignore arguments.count
                    $nextButton->click();
                    $page->waitForSelector('#ProductGridContainer');
                }
            }
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

            $page->goto($url, [
                'waitUntil' => 'networkidle2',
            ]);

            $details = $page->evaluate($this->makeFunction(<<<'JS'
                const details = Array.from(document.querySelectorAll('.about__wrapper:not(.no-js) .about__row span')).reduce((acc, current, index, array) => {
                    if (index % 2 === 0) {
                    acc[current.innerHTML.replace(/[\s\-]+/g, '_').toLowerCase()] = array[index + 1].innerHTML;
                    }
                    return acc;
                }, {});

                details['product_images'] = Array.from(document.querySelectorAll('.product__outer .product__media-sublist-outer .product__media img')).map(n => n.src)
                return details
            JS));

            data_set($details, 'url', $url);

            $this->details = $details;
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

    public function createSwitchFromRaw(RawKeySwitch $raw): KeySwitch
    {
        $disk = $this->disk;
        $switch = $raw->keySwitch()->firstOrNew();

        $cover = $raw->raw_cover;

        if ($raw->raw_cover) {
            $cover = $this->storeImageAsWebp($raw->raw_cover, 'covers', $disk);

            if ($switch->cover) {
                Storage::disk($disk)->delete($switch->cover);
            }
        }

        $product_images = data_get($raw->raw_data, 'product_images');
        if (! empty($product_images)) {
            foreach ($product_images as &$image) {
                $image = $this->storeImageAsWebp($image, 'products', $disk);
            }

            if ($switch->product_images) {
                foreach ($switch->product_images as $image) {
                    Storage::disk($disk)->delete($image);
                }
            }
        }

        $switch->forceFill([
            'name' => $raw->raw_name,
            'price' => $raw->raw_price,
            'manufacturer' => $raw->raw_manufacturer,
            'cover' => $cover,
            'product_images' => $product_images,
        ]);
        $switch->save();

        return $switch;
    }
}
