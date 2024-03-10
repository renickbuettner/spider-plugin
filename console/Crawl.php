<?php namespace Renick\Spider\Console;

use File;
use Illuminate\Console\Command;
use Renick\Spider\Classes\SiteCrawler;
use Renick\Spider\Classes\SiteCrawlLogger;
use Renick\Spider\Classes\SiteCrawlObserver;
use Site;
use System\Models\SiteDefinition;

/**
 * Crawl Command
 *
 * @link https://docs.octobercms.com/3.x/extend/console-commands.html
 */
class Crawl extends Command
{
    /**
     * @var string signature for the console command.
     */
    protected $signature = 'spider:crawl';

    /**
     * @var string description is the console command description
     */
    protected $description = 'Crawl your local installation, and warmup your caches.';

    protected $crawledUrls = [];
    protected $failedUrls = [];
    protected SiteCrawlLogger $logger;


    /**
     * handle executes the console command.
     */
    public function handle()
    {
        $this->output->block('[Renick.Spider]');
        $this->logger = new SiteCrawlLogger();
        $this->clearCache();

        foreach (Site::listEnabled() as /* @var SiteDefinition */ $site) {
            $this->output->section("Crawling Site ({$site->name})");
            $crawler = new SiteCrawler($site);
            if (!$crawler->isValid()) {
                $this->output->error('Invalid URL, can\'t be crawled.');
                continue;
            }

            $this->output->table(
                ['URL', 'Status Code'],
                [[$site->base_url, $crawler->getBaseUrlStatusCode()]]
            );

            $observer = $this->getCrawlObserver();
            $result = $crawler->startCrawling($observer);

            $this->output->success("Successfully crawled {$result->getTotalCrawlCount()} urls.");
            $this->getOutput()->newLine(2);
        }
    }

    protected function getCrawlObserver(): SiteCrawlObserver
    {
        $that = $this;

        return new SiteCrawlObserver(
            static function ($url, $statusCode, $foundOnUrl) use (&$that) {
                $that->output->writeln("Crawled: {$url} ({$statusCode})");
                $that->crawledUrls[] = [
                    'url' => $url,
                    'statusCode' => $statusCode,
                    'foundOnUrl' => $foundOnUrl
                ];
                $that->logger->logCrawl(false, $url, $statusCode, $foundOnUrl);
            },
            static function ($url, $statusCode, $foundOnUrl) use (&$that) {
                $that->output->writeln("Failed: {$url} ({$statusCode})");
                $that->failedUrls[] = [
                    'url' => $url,
                    'statusCode' => $statusCode,
                    'foundOnUrl' => $foundOnUrl
                ];
                $that->logger->logCrawl(true, $url, $statusCode, $foundOnUrl);
            }
        );
    }

    protected function clearCache(): void
    {
        if (!env('SPIDER_CACHE_CLEAR', true)) {
            return;
        }

        $this->output->section('Clearing Cache');

        $this->call('cache:clear');
        $this->call('view:clear');
        $this->clearResizerImages();

        if (class_exists('BizMark\Quicksilver\Console\Clear')) {
            $this->call('quicksilver:clear');
        }
    }

    /**
     * Clear resizer images, optionally.
     * @return void
     */
    protected function clearResizerImages(): void {
        if (!env('SPIDER_CACHE_CLEAR_RESIZER', false)) {
            return;
        }

        // see: modules/system/console/OctoberUtilCommands.php
        $path = base_path('storage/app/resources/resize');

        if (File::isDirectory($path)) {
            File::cleanDirectory($path);
        }
    }
}
