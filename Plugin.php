<?php namespace Renick\Spider;

use Backend;
use Renick\Spider\Console\Crawl;
use System\Classes\PluginBase;

/**
 * Plugin Information File
 *
 * @link https://docs.octobercms.com/3.x/extend/system/plugins.html
 */
class Plugin extends PluginBase
{
    public const CRAWLER_USER_AGENT = 'Renick.Spider';
    public const CRAWLER_CONCURRENCY = 10;
    public const CRAWLER_MAX_RESPONSE_SIZE = 1024 * 1024 * 25;
    public const CRAWLER_LOG_MODE = 'SINGLE';
    public const CRAWLER_LOG_FILE = 'logs/spider.log';
    public const CRAWLER_CRON_SCHEDULE = '1 1 * * *'; // see: https://crontab.guru/
    public const CRAWLER_CRON_ENABLED = false;


    /**
     * pluginDetails about this plugin.
     */
    public function pluginDetails()
    {
        return [
            'name' => 'renick.spider::lang.plugin.name',
            'description' => 'renick.spider::lang.plugin.description',
            'author' => 'Renick Büttner',
            'icon' => 'icon-bug'
        ];
    }

    public function register()
    {
        // no-op
    }

    public function registerSchedule($schedule): void
    {
        if (!env('SPIDER_CRON_ENABLED', self::CRAWLER_CRON_ENABLED)) {
            return;
        }

        $timing = env('SPIDER_CRON_SCHEDULE', self::CRAWLER_CRON_SCHEDULE);
        $schedule->command('spider:crawl')
            ->cron($timing)
            ->withoutOverlapping();
    }

    public function registerPermissions(): array
    {
        return [
            'renick.spider.overview' => [
                'tab'   => 'renick.spider::lang.permissions.tab',
                'label' => 'renick.spider::lang.permissions.label',
                'roles' => ['developer'],
            ],
        ];
    }

    public function registerNavigation(): array
    {
        return [
            'spider' => [
                'icon' => 'icon-bug',
                'label' => 'renick.spider::lang.plugin.name',
                'url' => Backend::url('renick/spider/overview/index'),
                'permissions' => ['renick.spider.overview'],
                'order' => 600,
            ]
        ];
    }

    public function boot()
    {
        $this->registerConsoleCommand('spider.crawl', Crawl::class);
    }

    public static function getCrawlerUserAgent(): string
    {
        return env('SPIDER_USER_AGENT', self::CRAWLER_USER_AGENT);
    }

    public static function getCrawlerConcurrency(): int
    {
        return (int) env('SPIDER_CONCURRENCY', self::CRAWLER_CONCURRENCY);
    }

    public static function getCrawlerMaxResponseSize(): int
    {
        return (int) env('SPIDER_MAX_RESPONSE_SIZE', self::CRAWLER_MAX_RESPONSE_SIZE);
    }

    /**
     * @return string 'single' | 'append' | 'failed'
     */
    public static function getLogMode(): string
    {
        return env('SPIDER_LOG_MODE', self::CRAWLER_LOG_MODE);
    }

    public static function getLogFile(): string
    {
        return env('SPIDER_LOG_FILE', self::CRAWLER_LOG_FILE);
    }
}
