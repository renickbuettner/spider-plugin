<?php namespace Renick\Spider\Classes;

use GuzzleHttp\RequestOptions;
use Http;
use Renick\Spider\Plugin;
use Spatie\Crawler\Crawler;
use Spatie\Crawler\CrawlObservers\CrawlObserver;
use Spatie\Crawler\CrawlProfiles\CrawlInternalUrls;
use Spatie\Crawler\CrawlProfiles\CrawlProfile;
use Spatie\Crawler\CrawlQueues\ArrayCrawlQueue;
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

    public function startCrawling(CrawlObserver $observer): Crawler
    {
        $crawler = $this->getCrawler($observer);
        $crawler->startCrawling($this->appUrl);
        return $crawler;
    }

    protected function getCrawler(CrawlObserver $observer): Crawler
    {
        $options = [
            RequestOptions::TIMEOUT => 10,
            RequestOptions::COOKIES => true,
            RequestOptions::VERIFY => false,
            RequestOptions::CONNECT_TIMEOUT => 10,
            RequestOptions::ALLOW_REDIRECTS => true,
            RequestOptions::HEADERS => ['User-Agent' => Plugin::getCrawlerUserAgent()],
        ];

        // ToDo: Do we need RedisCrawlQueue some day for large customers?
        $queue = new ArrayCrawlQueue();

        return Crawler::create($options)
            ->ignoreRobots()
            ->setCrawlQueue($queue)
            ->acceptNofollowLinks()
            ->addCrawlObserver($observer)
            ->setCrawlProfile($this->getCrawlProfile())
            ->setUserAgent(Plugin::getCrawlerUserAgent())
            ->setConcurrency(Plugin::getCrawlerConcurrency())
            ->setUrlParserClass(SiteLinkUrlParser::class)
            ->setMaximumResponseSize(Plugin::getCrawlerMaxResponseSize());
    }

    protected function getCrawlProfile(): CrawlProfile
    {
        return new CrawlInternalUrls($this->appUrl);
    }


}
