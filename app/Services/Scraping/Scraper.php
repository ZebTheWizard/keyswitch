<?php

namespace App\Services\Scraping;

use App\Enum\RawDataStatus;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Validator;
use Nesk\Puphpeteer\Puppeteer;
use Nesk\Rialto\Data\JsFunction;

abstract class Scraper
{
    /**
     * @var array<mixed>
     */
    private array $options = [];

    /**
     * @var int<0, max>
     */
    protected int $id;

    /**
     * @var array<mixed>
     */
    protected array $rawRecords;

    /**
     * @var array<mixed>
     */
    protected array $details;

    /**
     * @var array<mixed>
     */
    public array $jsonStack = [];

    final public function __construct() {}

    protected function invoke(?Closure $method = null, mixed ...$args): mixed
    {
        if ($method) {
            return $method->call($this, ...$args);
        }

        return null;
    }

    /**
     * @param  array<mixed>  $options
     */
    public function options(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    protected function makeFunction(string $js): JsFunction
    {
        // @phpstan-ignore method.staticCall
        return JsFunction::createWithBody($js);
    }

    protected function validateModel(Model $model, Validator $validator): void
    {
        if ($validator->fails()) {
            $errorMessages = implode("\n• ", $validator->errors()->all());
            $model->forceFill([
                'status' => RawDataStatus::NEEDS_REVIEW,
                'review_reason' => "Validation failed\n• {$errorMessages}",
            ]);
        } else {
            $model->forceFill([
                'status' => RawDataStatus::READY,
                'review_reason' => null,
            ]);
        }
    }

    protected function launch(?Closure $method = null): static
    {
        $scraper = \App\Models\Scraper::firstWhere(['class' => static::class]);
        throw_if(! $scraper, 'Could not find scraper registered in database.');

        $this->id = $scraper->id;

        $puppeteer = new Puppeteer([
            'executable_path' => config('app.node_path'),
            'log_node_console' => true,
            'logger' => new RialtoInterceptLogger($this),
        ]);
        $browser = $puppeteer->launch($this->options);
        $page = $browser->newPage();

        if ($method) {
            $method->call($this, $page);
        }

        $browser->close();

        return $this;
    }

    /**
     * @param  array<mixed>  $args
     */
    public static function make(array ...$args): static
    {
        return new static(...$args);
    }

    abstract public function scrapeListing(): static;

    abstract public function recordListing(?int $count = null): static;

    abstract public function scrapeDetails(string $url): static;

    abstract public function recordDetails(): static;
}
