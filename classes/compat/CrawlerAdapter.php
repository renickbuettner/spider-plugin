<?php namespace Renick\Spider\Classes\Compat;

use Renick\Spider\Classes\SiteCrawlObserver;

/**
 * Hides the API differences between spatie/crawler 8.x and 9.x.
 */
interface CrawlerAdapter
{
    /**
     * Creates a fully configured crawler for the given base url.
     */
    public function make(
        string $baseUrl,
        SiteCrawlObserver $observer,
        array $clientOptions,
        string $userAgent,
        int $concurrency,
        int $maxResponseSize
    ): object;

    /**
     * Runs the crawl and returns the total number of processed urls.
     */
    public function run(object $crawler, string $baseUrl): int;

    /**
     * Creates the crawl profile that accepts internal urls only.
     */
    public function internalProfile(string $baseUrl): object;
}
