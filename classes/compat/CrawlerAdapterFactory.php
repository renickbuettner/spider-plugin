<?php namespace Renick\Spider\Classes\Compat;

class CrawlerAdapterFactory
{
    /**
     * spatie/crawler 9.x introduced CrawlResponse; it does not exist in 8.x.
     */
    public static function isV9(): bool
    {
        return class_exists(\Spatie\Crawler\CrawlResponse::class);
    }

    public static function make(): CrawlerAdapter
    {
        return static::isV9() ? new SpatieV9Adapter() : new SpatieV8Adapter();
    }
}
