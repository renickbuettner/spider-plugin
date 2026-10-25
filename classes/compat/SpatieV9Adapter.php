<?php namespace Renick\Spider\Classes\Compat;

use Renick\Spider\Classes\SiteCrawlObserver;
use Spatie\Crawler\Crawler;
use Spatie\Crawler\CrawlProfiles\CrawlInternalUrls;

/**
 * spatie/crawler ^9.
 */
class SpatieV9Adapter implements CrawlerAdapter
{
    public function make(
        string $baseUrl,
        SiteCrawlObserver $observer,
        array $clientOptions,
        string $userAgent,
        int $concurrency,
        int $maxResponseSize
    ): object {
        return Crawler::create($baseUrl, $clientOptions)
            ->ignoreRobots()
            ->followNofollow()
            ->addObserver(new SpatieV9Observer($observer))
            ->crawlProfile($this->internalProfile($baseUrl))
            ->userAgent($userAgent)
            ->concurrency($concurrency)
            ->urlParser(new SpatieV9LinkUrlParser(false))
            ->maxResponseSizeInBytes($maxResponseSize);
    }

    public function run(object $crawler, string $baseUrl): int
    {
        $crawler->start();
        return $crawler->getCrawlProgress()->urlsProcessed;
    }

    public function internalProfile(string $baseUrl): object
    {
        return new CrawlInternalUrls($baseUrl);
    }
}
