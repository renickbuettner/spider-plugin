<?php namespace Renick\Spider\Classes;

use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * Version independent observer; spatie/crawler 8.x and 9.x call it through the
 * adapters in classes/compat.
 *
 * @method callbackCrawled(UriInterface $url, int $statusCode, UriInterface|null $foundOnUrl)
 * @method callbackFailed(UriInterface $url, int $statusCode, UriInterface|null $foundOnUrl)
 */
class SiteCrawlObserver
{
    protected $callbackCrawled;
    protected $callbackFailed;

    public function __construct(callable $callbackCrawled, callable $callbackFailed)
    {
        $this->callbackCrawled = $callbackCrawled;
        $this->callbackFailed = $callbackFailed;
    }


    /*
     * Called when the crawler has crawled the given url successfully.
     */
    public function crawled(
        UriInterface $url,
        ResponseInterface $response,
        ?UriInterface $foundOnUrl = null,
        ?string $linkText = null,
    ): void
    {
        if (!is_callable($this->callbackCrawled) ||
            empty($this->callbackCrawled)) {
            return;
        }

        ($this->callbackCrawled)($url, $response->getStatusCode(), $foundOnUrl);
    }

    /*
     * Called when the crawler had a problem crawling the given url.
     */
    public function crawlFailed(
        UriInterface $url,
        RequestException $requestException,
        ?UriInterface $foundOnUrl = null,
        ?string $linkText = null,
    ): void
    {
        if (!is_callable($this->callbackFailed)) {
            return;
        }

        ($this->callbackFailed)($url, $requestException->getCode(), $foundOnUrl);
    }

}
