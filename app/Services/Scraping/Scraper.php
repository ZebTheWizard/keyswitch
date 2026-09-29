<?php

namespace App\Services\Scraping;

use Closure;
use Nesk\Puphpeteer\Puppeteer;

class Scraper {

    private array $options = [];

    protected function invoke(Closure|null $method = null, ...$args) {
        if ($method) {
            $method->call($this, ...$args);
        }
    }

    public function options(array $options) {
        $this->options = $options;
        return $this;
    }

    protected function launch(Closure|null $method = null) {
        $url = "https://google.com";
        $puppeteer = new Puppeteer();
        $browser = $puppeteer->launch($this->options);
        $page = $browser->newPage();

        if ($method) {
            $method->call($this, $page);
        }

        $browser->close();

    }

    public static function make(...$args):self {
        return new self(...$args);
    }

}
