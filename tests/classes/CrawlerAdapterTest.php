<?php namespace Renick\Spider\Tests\Classes;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Renick\Spider\Classes\Compat\CrawlerAdapterFactory;
use Renick\Spider\Classes\SiteCrawlObserver;
use Renick\Spider\Tests\SpiderTestCase;

require_once __DIR__ . '/../SpiderTestCase.php';

/**
 * Runs a real crawl through the adapter with a fake http handler. Works with
 * spatie/crawler 8.x and 9.x.
 */
class CrawlerAdapterTest extends SpiderTestCase
{
    protected function crawl(): array
    {
        $pages = [
            '/' => '<html><head><link rel="stylesheet" href="/style.css"></head><body>'
                . '<a href="/about">A</a><a href="/missing">M</a><a href="https://other.test/x">E</a>'
                . '<a href="mailto:a@b.test">mail</a><a href="/about#top">dup</a></body></html>',
            '/about' => '<html><body><a href="/">home</a></body></html>',
            '/style.css' => 'body{}',
        ];
        $handler = HandlerStack::create(function (RequestInterface $request) use ($pages) {
            $path = $request->getUri()->getPath();
            if (isset($pages[$path])) {
                return Create::promiseFor(new Response(200, ['Content-Type' => 'text/html'], $pages[$path]));
            }
            return Create::promiseFor(new Response(404, [], 'nope'));
        });

        $crawled = [];
        $failed = [];
        $observer = new SiteCrawlObserver(
            function ($url, $status, $foundOn) use (&$crawled) { $crawled[(string) $url] = [$status, $foundOn === null ? null : (string) $foundOn]; },
            function ($url, $status, $foundOn) use (&$failed) { $failed[(string) $url] = [$status, $foundOn === null ? null : (string) $foundOn]; }
        );

        $adapter = CrawlerAdapterFactory::make();
        $crawler = $adapter->make('http://site.test/', $observer, ['handler' => $handler], 'UA/1', 2, 1024 * 1024);
        $total = $adapter->run($crawler, 'http://site.test/');

        return [$crawled, $failed, $total];
    }

    public function testCrawlReportsCrawledAndFailedUrls(): void
    {
        [$crawled, $failed, $total] = $this->crawl();

        $this->assertEqualsCanonicalizing(
            ['http://site.test/', 'http://site.test/about', 'http://site.test/style.css'],
            array_keys($crawled)
        );
        $this->assertSame(200, $crawled['http://site.test/about'][0]);
        $this->assertSame('http://site.test/', $crawled['http://site.test/about'][1]);
        $this->assertSame(['http://site.test/missing'], array_keys($failed));
        $this->assertSame(404, $failed['http://site.test/missing'][0]);
        $this->assertSame('http://site.test/', $failed['http://site.test/missing'][1]);
        $this->assertSame(4, $total);
    }
}
