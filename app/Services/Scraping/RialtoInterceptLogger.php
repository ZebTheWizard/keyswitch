<?php

namespace App\Services\Scraping;

use Exception;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

class RialtoInterceptLogger implements LoggerInterface
{
    public function __construct(public Scraper $scraper) {}

    /**
     * Logs with an arbitrary level.
     *
     * @param  mixed  $level
     * @param  mixed[]  $context
     *
     * @throws InvalidArgumentException
     */
    public function log($level, string|\Stringable $message, array $context = []): void
    {
        if (str($message)->contains('Node')) {
            $payload = json_decode(base64_decode(trim(str($message)->explode(':')->get(1))), associative: true);
            array_unshift($this->scraper->jsonStack, $payload);
        }
    }

    /**
     * System is unusable.
     *
     * @param  mixed[]  $context
     */
    public function emergency(string|\Stringable $message, array $context = []): void
    {
        throw new Exception('Log level [emergency] no implemented for RialtoInterceptLogger.');
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param  mixed[]  $context
     */
    public function alert(string|\Stringable $message, array $context = []): void
    {
        throw new Exception('Log level [alert] no implemented for RialtoInterceptLogger.');
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param  mixed[]  $context
     */
    public function critical(string|\Stringable $message, array $context = []): void
    {
        throw new Exception('Log level [critical] no implemented for RialtoInterceptLogger.');
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param  mixed[]  $context
     */
    public function error(string|\Stringable $message, array $context = []): void
    {
        throw new Exception('Log level [error] no implemented for RialtoInterceptLogger.');
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param  mixed[]  $context
     */
    public function warning(string|\Stringable $message, array $context = []): void
    {
        throw new Exception('Log level [warning] no implemented for RialtoInterceptLogger.');
    }

    /**
     * Normal but significant events.
     *
     * @param  mixed[]  $context
     */
    public function notice(string|\Stringable $message, array $context = []): void
    {
        throw new Exception('Log level [notice] no implemented for RialtoInterceptLogger.');
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param  mixed[]  $context
     */
    public function info(string|\Stringable $message, array $context = []): void
    {
        throw new Exception('Log level [info] no implemented for RialtoInterceptLogger.');
    }

    /**
     * Detailed debug information.
     *
     * @param  mixed[]  $context
     */
    public function debug(string|\Stringable $message, array $context = []): void
    {
        throw new Exception('Log level [debug] no implemented for RialtoInterceptLogger.');
    }
}
