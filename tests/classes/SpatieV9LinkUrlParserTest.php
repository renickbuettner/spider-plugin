<?php namespace Renick\Spider\Tests\Classes;

use Renick\Spider\Classes\Compat\CrawlerAdapterFactory;
use Renick\Spider\Classes\Compat\SpatieV9LinkUrlParser;
use Renick\Spider\Tests\SpiderTestCase;

require_once __DIR__ . '/../SpiderTestCase.php';

class SpatieV9LinkUrlParserTest extends SpiderTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        if (!CrawlerAdapterFactory::isV9()) {
            $this->markTestSkipped('Only relevant for spatie/crawler 9.');
        }
    }

    protected function urls(string $html, bool $rejectNofollow = false): array
    {
        $parser = new SpatieV9LinkUrlParser($rejectNofollow);
        return array_map(fn ($u) => $u->url, $parser->extractUrls($html, 'http://site.test/'));
    }

    public function testLinkTagsAreFollowed(): void
    {
        $this->assertSame(['http://site.test/style.css'], $this->urls('<link rel="stylesheet" href="/style.css">'));
    }

    public function testResolvesRelativeStripsFragmentsAndSkipsOtherSchemes(): void
    {
        $this->assertEqualsCanonicalizing(
            ['http://site.test/about', 'http://site.test/contact', 'https://other.test/'],
            $this->urls('<a href="/about#top">A</a><a href="contact">C</a><a href="https://other.test/">E</a>'
                . '<a href="mailto:a@b.test">M</a><a href="tel:123">T</a>')
        );
    }

    public function testNofollowFollowedUnlessRejected(): void
    {
        $html = '<a href="/nf" rel="nofollow">N</a>';
        $this->assertSame(['http://site.test/nf'], $this->urls($html));
        $this->assertSame([], $this->urls($html, true));
    }
}
