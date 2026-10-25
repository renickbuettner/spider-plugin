<?php namespace Renick\Spider\Classes\Compat;

use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Renick\Spider\Classes\SiteCrawlObserver;
use Spatie\Crawler\CrawlObservers\CrawlObserver;

/**
 * Bridges spatie/crawler ^8 observer calls to SiteCrawlObserver.
 */
class SpatieV8Observer extends CrawlObserver
{
    public function __construct(protected SiteCrawlObserver $observer)
    {
    }

    public function crawled(
        UriInterface $url,
        ResponseInterface $response,
        ?UriInterface $foundOnUrl = null,
        ?string $linkText = null,
    ): void {
        $this->observer->crawled($url, $response, $foundOnUrl, $linkText);
    }

    public function crawlFailed(
        UriInterface $url,
        RequestException $requestException,
        ?UriInterface $foundOnUrl = null,
        ?string $linkText = null,
    ): void {
        $this->observer->crawlFailed($url, $requestException, $foundOnUrl, $linkText);
    }
}
