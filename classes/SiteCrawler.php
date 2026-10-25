<?php namespace Renick\Spider\Classes;

use GuzzleHttp\RequestOptions;
use Http;
use Renick\Spider\Classes\Compat\CrawlerAdapter;
use Renick\Spider\Classes\Compat\CrawlerAdapterFactory;
use Renick\Spider\Plugin;
use System\Models\SiteDefinition;

class SiteCrawler
{
    protected SiteDefinition $site;
    protected ?string $appUrl;

    public function __construct(SiteDefinition $site)
    {
        $this->site = $site;
        $this->appUrl = $site->base_url ?? null;
    }

    protected function getBaseUrlResponse(): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return Http::withOptions(['verify' => false])->get($this->appUrl);
    }

    public function isValid(): bool
    {
        if (!$this->appUrl ||
            !filter_var($this->appUrl, FILTER_VALIDATE_URL)) {
            return false;
        }

        return $this->getBaseUrlResponse()->successful();
    }

    public function getBaseUrlStatusCode(): int
    {
        return $this->getBaseUrlResponse()->status();
    }

    /**
     * Crawls the site and returns the total number of processed urls.
     */
    public function startCrawling(SiteCrawlObserver $observer): int
    {
        $crawler = $this->getCrawler($observer);
        return $this->adapter()->run($crawler, $this->appUrl);
    }

    protected function adapter(): CrawlerAdapter
    {
        return CrawlerAdapterFactory::make();
    }

    protected function getCrawler(SiteCrawlObserver $observer): object
    {
        $options = [
            RequestOptions::TIMEOUT => 10,
            RequestOptions::COOKIES => true,
            RequestOptions::VERIFY => false,
            RequestOptions::CONNECT_TIMEOUT => 10,
            RequestOptions::ALLOW_REDIRECTS => true,
            RequestOptions::HEADERS => ['User-Agent' => Plugin::getCrawlerUserAgent()],
        ];

        return $this->adapter()->make(
            $this->appUrl,
            $observer,
            $options,
            Plugin::getCrawlerUserAgent(),
            Plugin::getCrawlerConcurrency(),
            Plugin::getCrawlerMaxResponseSize()
        );
    }

    protected function getCrawlProfile(): object
    {
        return $this->adapter()->internalProfile($this->appUrl);
    }


}
