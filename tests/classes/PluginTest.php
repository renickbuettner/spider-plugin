<?php namespace Renick\Spider\Tests\Classes;

use Renick\Spider\Plugin;
use Renick\Spider\Tests\SpiderTestCase;

require_once __DIR__ . '/../SpiderTestCase.php';

class PluginTest extends SpiderTestCase
{
    protected function plugin(): Plugin
    {
        return new Plugin($this->app);
    }

    public function testConstants(): void
    {
        $this->assertSame('Renick.Spider', Plugin::CRAWLER_USER_AGENT);
        $this->assertSame(10, Plugin::CRAWLER_CONCURRENCY);
        $this->assertSame(25 * 1024 * 1024, Plugin::CRAWLER_MAX_RESPONSE_SIZE);
        $this->assertSame('SINGLE', Plugin::CRAWLER_LOG_MODE);
        $this->assertSame('logs/spider.log', Plugin::CRAWLER_LOG_FILE);
        $this->assertSame('1 1 * * *', Plugin::CRAWLER_CRON_SCHEDULE);
        $this->assertFalse(Plugin::CRAWLER_CRON_ENABLED);
    }

    public function testGettersReturnDefaultsWithoutEnv(): void
    {
        foreach (['SPIDER_USER_AGENT', 'SPIDER_CONCURRENCY', 'SPIDER_MAX_RESPONSE_SIZE', 'SPIDER_LOG_MODE', 'SPIDER_LOG_FILE'] as $k) {
            $this->setEnv($k, null);
        }
        $this->assertSame('Renick.Spider', Plugin::getCrawlerUserAgent());
        $this->assertSame(10, Plugin::getCrawlerConcurrency());
        $this->assertSame(26214400, Plugin::getCrawlerMaxResponseSize());
        $this->assertSame('SINGLE', Plugin::getLogMode());
        $this->assertSame('logs/spider.log', Plugin::getLogFile());
    }

    public function testGettersHonourEnvOverrides(): void
    {
        $this->setEnv('SPIDER_USER_AGENT', 'Custom/1.0');
        $this->setEnv('SPIDER_CONCURRENCY', '3');
        $this->setEnv('SPIDER_MAX_RESPONSE_SIZE', '2048');
        $this->setEnv('SPIDER_LOG_MODE', 'append');
        $this->setEnv('SPIDER_LOG_FILE', 'logs/other.log');

        $this->assertSame('Custom/1.0', Plugin::getCrawlerUserAgent());
        $this->assertSame(3, Plugin::getCrawlerConcurrency());
        $this->assertSame(2048, Plugin::getCrawlerMaxResponseSize());
        $this->assertSame('append', Plugin::getLogMode());
        $this->assertSame('logs/other.log', Plugin::getLogFile());
    }

    public function testPluginDetails(): void
    {
        $d = $this->plugin()->pluginDetails();
        $this->assertSame('renick.spider::lang.plugin.name', $d['name']);
        $this->assertSame('icon-bug', $d['icon']);
    }

    public function testRegisterPermissions(): void
    {
        $p = $this->plugin()->registerPermissions();
        $this->assertSame(['renick.spider.overview'], array_keys($p));
        $this->assertSame(['developer'], $p['renick.spider.overview']['roles']);
        $this->assertArrayHasKey('tab', $p['renick.spider.overview']);
        $this->assertArrayHasKey('label', $p['renick.spider.overview']);
    }

    public function testRegisterNavigation(): void
    {
        $n = $this->plugin()->registerNavigation();
        $this->assertSame(['spider'], array_keys($n));
        $this->assertSame(['renick.spider.overview'], $n['spider']['permissions']);
        $this->assertStringEndsWith('renick/spider/overview/index', $n['spider']['url']);
        $this->assertSame('icon-bug', $n['spider']['icon']);
        $this->assertSame(600, $n['spider']['order']);
    }

    public function testScheduleIsNotRegisteredByDefault(): void
    {
        $this->setEnv('SPIDER_CRON_ENABLED', null);
        $schedule = new \Illuminate\Console\Scheduling\Schedule();
        $this->plugin()->registerSchedule($schedule);
        $this->assertCount(0, $schedule->events());
    }

    public function testScheduleRegisteredWhenEnabled(): void
    {
        $this->setEnv('SPIDER_CRON_ENABLED', 'true');
        $this->setEnv('SPIDER_CRON_SCHEDULE', '5 4 * * *');
        $schedule = new \Illuminate\Console\Scheduling\Schedule();
        $this->plugin()->registerSchedule($schedule);

        $events = $schedule->events();
        $this->assertCount(1, $events);
        $this->assertSame('5 4 * * *', $events[0]->expression);
        $this->assertStringContainsString('spider:crawl', $events[0]->command);
        $this->assertTrue($events[0]->withoutOverlapping);
    }

    public function testScheduleUsesDefaultCronExpression(): void
    {
        $this->setEnv('SPIDER_CRON_ENABLED', 'true');
        $this->setEnv('SPIDER_CRON_SCHEDULE', null);
        $schedule = new \Illuminate\Console\Scheduling\Schedule();
        $this->plugin()->registerSchedule($schedule);
        $this->assertSame('1 1 * * *', $schedule->events()[0]->expression);
    }

    public function testCrawlCommandIsRegistered(): void
    {
        $this->assertArrayHasKey('spider:crawl', \Artisan::all());
    }
}
