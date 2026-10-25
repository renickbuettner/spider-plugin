<?php namespace Renick\Spider\Tests\Classes;

use GuzzleHttp\Psr7\Uri;
use ReflectionProperty;
use Renick\Spider\Classes\Compat\CrawlerAdapterFactory;
use Renick\Spider\Classes\SiteLinkUrlParser;
use Renick\Spider\Tests\SpiderTestCase;
use Spatie\Crawler\Crawler;
use Spatie\Crawler\CrawlProfiles\CrawlInternalUrls;
use Spatie\Crawler\CrawlQueues\ArrayCrawlQueue;

require_once __DIR__ . '/../SpiderTestCase.php';

class SiteLinkUrlParserTest extends SpiderTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        if (CrawlerAdapterFactory::isV9()) {
            $this->markTestSkipped('SiteLinkUrlParser is used with spatie/crawler 8 only; see SpatieV9LinkUrlParserTest.');
        }
    }

    protected function queuedUrls(string $html, bool $rejectNofollow = false): array
    {
        $crawler = Crawler::create()
            ->setCrawlQueue(new ArrayCrawlQueue())
            ->setCrawlProfile(new CrawlInternalUrls('http://site.test'))
            ->ignoreRobots();
        $rejectNofollow ? $crawler->rejectNofollowLinks() : $crawler->acceptNofollowLinks();

        $parser = new SiteLinkUrlParser($crawler);
        $parser->addFromHtml($html, new Uri('http://site.test/'));

        $prop = new ReflectionProperty(ArrayCrawlQueue::class, 'pendingUrls');
        $prop->setAccessible(true);
        return array_values(array_map(fn ($c) => (string) $c->url, $prop->getValue($crawler->getCrawlQueue())));
    }

    public function testFollowsInternalLinksAndResolvesRelative(): void
    {
        $urls = $this->queuedUrls('<a href="/about">A</a><a href="contact">C</a><a href="http://site.test/x">X</a>');
        $this->assertEqualsCanonicalizing(
            ['http://site.test/about', 'http://site.test/contact', 'http://site.test/x'],
            $urls
        );
    }

    public function testIgnoresExternalAndNonHttpSchemes(): void
    {
        $urls = $this->queuedUrls(
            '<a href="https://other.test/">E</a><a href="mailto:a@b.test">M</a>'
            . '<a href="tel:123">T</a><a href="javascript:void(0)">J</a><a href="/ok">ok</a>'
        );
        $this->assertSame(['http://site.test/ok'], $urls);
    }

    public function testFragmentsAreStripped(): void
    {
        $urls = $this->queuedUrls('<a href="/about#top">A</a>');
        $this->assertSame(['http://site.test/about'], $urls);
    }

    public function testDuplicatesAreQueuedOnce(): void
    {
        $urls = $this->queuedUrls('<a href="/a">1</a><a href="/a">2</a><a href="/a#x">3</a>');
        $this->assertSame(['http://site.test/a'], $urls);
    }

    public function testNofollowFollowedUnlessRejected(): void
    {
        $html = '<a href="/nf" rel="nofollow">N</a>';
        $this->assertSame(['http://site.test/nf'], $this->queuedUrls($html));
        $this->assertSame([], $this->queuedUrls($html, true));
    }

    public function testLinkTagsAreFollowed(): void
    {
        $urls = $this->queuedUrls('<link rel="stylesheet" href="/style.css">');
        $this->assertSame(['http://site.test/style.css'], $urls);
    }

    public function testEmptyAnchorWithoutSiblingIsSkipped(): void
    {
        // isInvalidHrefNode(): empty <a> without next sibling is ignored
        $this->assertSame([], $this->queuedUrls('<a href="/empty"></a>'));
    }
}
