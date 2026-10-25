<?php namespace Renick\Spider\Classes\Compat;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Uri;
use Renick\Spider\Classes\SiteCrawlObserver;
use Spatie\Crawler\CrawlObservers\CrawlObserver;
use Spatie\Crawler\CrawlProgress;
use Spatie\Crawler\CrawlResponse;
use Spatie\Crawler\Enums\ResourceType;
use Spatie\Crawler\TransferStatistics;

/**
 * Bridges spatie/crawler ^9 observer calls to SiteCrawlObserver.
 */
class SpatieV9Observer extends CrawlObserver
{
    public function __construct(protected SiteCrawlObserver $observer)
    {
    }

    public function crawled(
        string $url,
        CrawlResponse $response,
        CrawlProgress $progress,
    ): void {
        $this->observer->crawled(
            new Uri($url),
            $response->toPsrResponse(),
            $this->uri($response->foundOnUrl()),
            $response->linkText()
        );
    }

    public function crawlFailed(
        string $url,
        RequestException $requestException,
        CrawlProgress $progress,
        ?string $foundOnUrl = null,
        ?string $linkText = null,
        ?ResourceType $resourceType = null,
        ?TransferStatistics $transferStats = null,
    ): void {
        $this->observer->crawlFailed(
            new Uri($url),
            $requestException,
            $this->uri($foundOnUrl),
            $linkText
        );
    }

    protected function uri(?string $url): ?Uri
    {
        return $url === null ? null : new Uri($url);
    }
}
