<?php namespace Renick\Spider\Tests\Classes;

use Renick\Spider\Classes\SiteCrawlLogger;
use Renick\Spider\Tests\SpiderTestCase;

require_once __DIR__ . '/../SpiderTestCase.php';

class SiteCrawlLoggerTest extends SpiderTestCase
{
    protected string $file;

    public function setUp(): void
    {
        parent::setUp();
        $this->file = storage_path('logs/spider-phpunit.log');
        $this->setEnv('SPIDER_LOG_FILE', 'logs/spider-phpunit.log');
        @unlink($this->file);
    }

    public function tearDown(): void
    {
        @unlink($this->file);
        parent::tearDown();
    }

    public function testLogLineFormat(): void
    {
        $this->setEnv('SPIDER_LOG_MODE', 'single');
        $logger = new SiteCrawlLogger();
        $logger->logCrawl(false, 'http://a.test/', 200, 'http://a.test/x');
        $logger->logCrawl(true, 'http://a.test/missing', 404);

        $this->assertSame(
            "[CRAWLED]:(200) http://a.test/\n[FAILED]:(404) http://a.test/missing\n",
            file_get_contents($this->file)
        );
    }

    public function testSingleModeTruncatesExistingLog(): void
    {
        file_put_contents($this->file, "old\n");
        $this->setEnv('SPIDER_LOG_MODE', 'single');
        new SiteCrawlLogger();
        $this->assertSame('', file_get_contents($this->file));
    }

    public function testModeIsCaseInsensitive(): void
    {
        file_put_contents($this->file, "old\n");
        $this->setEnv('SPIDER_LOG_MODE', 'SINGLE');
        new SiteCrawlLogger();
        $this->assertSame('', file_get_contents($this->file));
    }

    public function testFailedModeTruncatesExistingLog(): void
    {
        file_put_contents($this->file, "old\n");
        $this->setEnv('SPIDER_LOG_MODE', 'failed');
        new SiteCrawlLogger();
        $this->assertSame('', file_get_contents($this->file));
    }

    public function testAppendModeKeepsExistingLog(): void
    {
        file_put_contents($this->file, "old\n");
        $this->setEnv('SPIDER_LOG_MODE', 'append');
        $logger = new SiteCrawlLogger();
        $logger->logCrawl(false, 'http://a.test/', 200);
        $this->assertSame("old\n[CRAWLED]:(200) http://a.test/\n", file_get_contents($this->file));
    }

    public function testFailedModeOnlyLogsFailures(): void
    {
        $this->setEnv('SPIDER_LOG_MODE', 'failed');
        $logger = new SiteCrawlLogger();
        $logger->logCrawl(false, 'http://a.test/', 200);
        $logger->logCrawl(true, 'http://a.test/missing', 404);
        $this->assertSame("[FAILED]:(404) http://a.test/missing\n", file_get_contents($this->file));
    }

    public function testAppendModeRotatesAboveMaxSize(): void
    {
        file_put_contents($this->file, str_repeat('x', 200));
        $this->setEnv('SPIDER_LOG_MODE', 'append');
        $this->setEnv('SPIDER_LOG_MAX_SIZE', '100');
        $logger = new SiteCrawlLogger();
        $logger->logCrawl(false, 'http://a.test/', 200);
        $this->assertSame("[CRAWLED]:(200) http://a.test/\n", file_get_contents($this->file));
        $this->assertSame(200, filesize($this->file . '.1'));
        @unlink($this->file . '.1');
    }

    public function testAppendModeDoesNotRotateBelowMaxSize(): void
    {
        file_put_contents($this->file, "old\n");
        $this->setEnv('SPIDER_LOG_MODE', 'append');
        $this->setEnv('SPIDER_LOG_MAX_SIZE', '100');
        new SiteCrawlLogger();
        $this->assertFileDoesNotExist($this->file . '.1');
    }
}
