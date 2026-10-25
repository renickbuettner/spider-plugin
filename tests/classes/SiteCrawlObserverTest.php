<?php namespace Renick\Spider\Tests\Classes;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use Renick\Spider\Classes\SiteCrawlObserver;
use Renick\Spider\Tests\SpiderTestCase;

require_once __DIR__ . '/../SpiderTestCase.php';

class SiteCrawlObserverTest extends SpiderTestCase
{
    public function testCrawledInvokesSuccessCallback(): void
    {
        $calls = [];
        $o = new SiteCrawlObserver(
            function (...$a) use (&$calls) { $calls['ok'] = $a; },
            function (...$a) use (&$calls) { $calls['fail'] = $a; }
        );
        $o->crawled(new Uri('http://a.test/'), new Response(200), new Uri('http://a.test/x'));

        $this->assertArrayNotHasKey('fail', $calls);
        $this->assertSame(200, $calls['ok'][1]);
        $this->assertSame('http://a.test/', (string) $calls['ok'][0]);
    }

    public function testCrawlFailedInvokesFailureCallbackWithResponseCode(): void
    {
        $calls = [];
        $o = new SiteCrawlObserver(
            function (...$a) use (&$calls) { $calls['ok'] = $a; },
            function (...$a) use (&$calls) { $calls['fail'] = $a; }
        );
        $req = new Request('GET', 'http://a.test/missing');
        $o->crawlFailed(new Uri('http://a.test/missing'), new RequestException('Not Found', $req, new Response(404)));

        $this->assertArrayNotHasKey('ok', $calls);
        $this->assertSame(404, $calls['fail'][1]);
    }
}
