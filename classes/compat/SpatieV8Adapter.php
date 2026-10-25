<?php namespace Renick\Spider\Classes\Compat;

use GuzzleHttp\RequestOptions;
use Renick\Spider\Classes\SiteCrawlObserver;
use Renick\Spider\Classes\SiteLinkUrlParser;
use Spatie\Crawler\Crawler;
use Spatie\Crawler\CrawlProfiles\CrawlInternalUrls;
use Spatie\Crawler\CrawlQueues\ArrayCrawlQueue;

/**
 * spatie/crawler ^8.
 */
class SpatieV8Adapter implements CrawlerAdapter
{
    public function make(
        string $baseUrl,
        SiteCrawlObserver $observer,
        array $clientOptions,
        string $userAgent,
        int $concurrency,
        int $maxResponseSize
    ): object {
        // ToDo: Do we need RedisCrawlQueue some day for large customers?
        return Crawler::create($clientOptions)
            ->ignoreRobots()
            ->setCrawlQueue(new ArrayCrawlQueue())
            ->acceptNofollowLinks()
            ->addCrawlObserver(new SpatieV8Observer($observer))
            ->setCrawlProfile($this->internalProfile($baseUrl))
            ->setUserAgent($userAgent)
            ->setConcurrency($concurrency)
            ->setUrlParserClass(SiteLinkUrlParser::class)
            ->setMaximumResponseSize($maxResponseSize);
    }

    public function run(object $crawler, string $baseUrl): int
    {
        $crawler->startCrawling($baseUrl);
        return $crawler->getTotalCrawlCount();
    }

    public function internalProfile(string $baseUrl): object
    {
        return new CrawlInternalUrls($baseUrl);
    }
}
