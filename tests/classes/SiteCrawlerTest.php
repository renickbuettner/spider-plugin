<?php namespace Renick\Spider\Tests\Classes;

use Http;
use Renick\Spider\Classes\Compat\CrawlerAdapterFactory;
use Renick\Spider\Classes\SiteCrawler;
use Renick\Spider\Tests\SpiderTestCase;
use Spatie\Crawler\CrawlProfiles\CrawlInternalUrls;
use System\Models\SiteDefinition;

require_once __DIR__ . '/../SpiderTestCase.php';

class SiteCrawlerTest extends SpiderTestCase
{
    protected function site(?string $url): SiteDefinition
    {
        $site = new SiteDefinition();
        // base_url is a computed attribute (custom app_url, rtrim'd)
        $site->is_custom_url = true;
        $site->app_url = $url;
        return $site;
    }

    public function testEmptyUrlIsInvalidWithoutRequest(): void
    {
        Http::fake();
        $this->assertFalse((new SiteCrawler($this->site('')))->isValid());
        Http::assertNothingSent();
    }

    public function testMalformedUrlIsInvalidWithoutRequest(): void
    {
        Http::fake();
        $this->assertFalse((new SiteCrawler($this->site('not a url')))->isValid());
        Http::assertNothingSent();
    }

    public function testSuccessfulResponseIsValid(): void
    {
        Http::fake(['site.test*' => Http::response('ok', 200)]);
        $crawler = new SiteCrawler($this->site('http://site.test/'));
        $this->assertTrue($crawler->isValid());
        $this->assertSame(200, $crawler->getBaseUrlStatusCode());
    }

    public function testErrorResponseIsInvalid(): void
    {
        Http::fake(['site.test*' => Http::response('nope', 500)]);
        $crawler = new SiteCrawler($this->site('http://site.test/'));
        $this->assertFalse($crawler->isValid());
        $this->assertSame(500, $crawler->getBaseUrlStatusCode());
    }

    public function testNotFoundBaseUrlIsInvalid(): void
    {
        Http::fake(['site.test*' => Http::response('', 404)]);
        $crawler = new SiteCrawler($this->site('http://site.test/'));
        $this->assertFalse($crawler->isValid());
        $this->assertSame(404, $crawler->getBaseUrlStatusCode());
    }

    public function testCrawlProfileOnlyAcceptsInternalUrls(): void
    {
        $crawler = new SiteCrawler($this->site('http://site.test/'));
        $m = new \ReflectionMethod($crawler, 'getCrawlProfile');
        $m->setAccessible(true);
        $profile = $m->invoke($crawler);

        $this->assertInstanceOf(CrawlInternalUrls::class, $profile);
        // spatie/crawler 8 takes a UriInterface, 9 a string
        $make = fn (string $u) => CrawlerAdapterFactory::isV9() ? $u : new \GuzzleHttp\Psr7\Uri($u);
        $this->assertTrue($profile->shouldCrawl($make('http://site.test/about')));
        $this->assertFalse($profile->shouldCrawl($make('http://other.test/about')));
    }

    public function testCrawlerIsConfiguredFromPluginSettings(): void
    {
        $this->setEnv('SPIDER_CONCURRENCY', '4');
        $this->setEnv('SPIDER_USER_AGENT', 'UA/9');
        $crawler = new SiteCrawler($this->site('http://site.test/'));
        $m = new \ReflectionMethod($crawler, 'getCrawler');
        $m->setAccessible(true);
        $c = $m->invoke($crawler, new \Renick\Spider\Classes\SiteCrawlObserver(fn () => null, fn () => null));

        $this->assertSame('UA/9', $c->getUserAgent());
        if (CrawlerAdapterFactory::isV9()) {
            $this->assertInstanceOf(\Renick\Spider\Classes\Compat\SpatieV9LinkUrlParser::class, $c->getUrlParser());
        } else {
            $this->assertSame(\Renick\Spider\Classes\SiteLinkUrlParser::class, $c->getUrlParserClass());
        }
        $this->assertFalse($c->mustRespectRobots());
        $this->assertFalse($c->mustRejectNofollowLinks());
    }

    public function testConnectionErrorMakesSiteInvalidInsteadOfThrowing(): void
    {
        \Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('refused');
        });
        $this->assertFalse((new SiteCrawler($this->site('http://a.test')))->isValid());
    }
}
