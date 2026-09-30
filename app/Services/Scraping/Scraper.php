<?php

namespace App\Services\Scraping;

use Closure;
use Nesk\Puphpeteer\Puppeteer;
use Nesk\Rialto\Data\JsFunction;

class Scraper
{
    /**
     * @var array<mixed>
     */
    private array $options = [];

    protected int $id;

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

    protected function launch(?Closure $method = null): static
    {
        $scraper = \App\Models\Scraper::firstWhere(['class' => static::class]);
        throw_if(! $scraper, 'Could not find scraper registered in database.');

        $this->id = $scraper->id;

        $url = 'https://google.com';
        $puppeteer = new Puppeteer;
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
}
