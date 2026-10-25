<?php namespace Renick\Spider\Classes\Compat;

use Spatie\Crawler\UrlParsers\LinkUrlParser;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

/**
 * Like SiteLinkUrlParser for spatie/crawler ^8: follows every <a> and <link> tag
 * (stylesheets, icons, ...), not only rel=next/prev/hreflang links.
 */
class SpatieV9LinkUrlParser extends LinkUrlParser
{
    protected function extractLinks(DomCrawler $domCrawler, string $baseUrl): array
    {
        $urls = [];

        foreach ($domCrawler->filterXpath('//a | //link')->links() as $link) {
            $urls[] = $this->processLink($link, $baseUrl);
        }

        return array_filter($urls);
    }
}
