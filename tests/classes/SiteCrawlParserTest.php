<?php namespace Renick\Spider\Tests\Classes;

use Renick\Spider\Classes\SiteCrawlParser;
use Renick\Spider\Tests\SpiderTestCase;

require_once __DIR__ . '/../SpiderTestCase.php';

class SiteCrawlParserTest extends SpiderTestCase
{
    protected string $file;

    public function setUp(): void
    {
        parent::setUp();
        $this->file = storage_path('logs/spider-phpunit.log');
        @unlink($this->file);
    }

    public function tearDown(): void
    {
        @unlink($this->file);
        parent::tearDown();
    }

    public function testMissingFileYieldsEmptyResult(): void
    {
        $parser = new SiteCrawlParser();
        $this->assertSame([], $parser->parse());
        $this->assertNull($parser->getDate());
    }

    public function testParsesCrawledAndFailedLines(): void
    {
        file_put_contents($this->file, implode(PHP_EOL, [
            '[CRAWLED]:(200) http://127.0.0.1:8000/',
            '[CRAWLED]:(200) http://127.0.0.1:8000/about',
            '[FAILED]:(404) http://127.0.0.1:8000/does-not-exist',
            '',
        ]));

        $rows = (new SiteCrawlParser())->parse();

        $this->assertCount(3, $rows);
        $this->assertEquals(200, $rows[0]['status_code']);
        $this->assertSame('http://127.0.0.1:8000/', $rows[0]['url']);
        $this->assertSame('http://127.0.0.1:8000/about', $rows[1]['url']);
        $this->assertEquals(404, $rows[2]['status_code']);
        $this->assertSame('http://127.0.0.1:8000/does-not-exist', $rows[2]['url']);
    }

    public function testUnparsableLineGetsFallbackValues(): void
    {
        file_put_contents($this->file, "garbage line\n");
        $rows = (new SiteCrawlParser())->parse();
        $this->assertSame([['status_code' => -1, 'url' => '-']], $rows);
    }

    public function testGetDateReturnsFileMtime(): void
    {
        file_put_contents($this->file, "[CRAWLED]:(200) http://a.test/\n");
        touch($this->file, 1700000000);
        clearstatcache();
        $date = (new SiteCrawlParser())->getDate();
        $this->assertSame(1700000000, $date->getTimestamp());
    }

    /**
     * Documents current behaviour: the URL regex has no "?", "&", "=" or "%",
     * so query strings are cut off in the overview.
     */
    public function testQueryStringIsTruncatedByRegex(): void
    {
        file_put_contents($this->file, "[CRAWLED]:(200) http://a.test/page?x=1&y=2\n");
        $rows = (new SiteCrawlParser())->parse();
        $this->assertSame('http://a.test/page', $rows[0]['url']);
    }
}
