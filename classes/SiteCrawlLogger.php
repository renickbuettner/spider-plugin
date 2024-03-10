<?php namespace Renick\Spider\Classes;

use Renick\Spider\Plugin;
use Str;

class SiteCrawlLogger
{
    protected string $logMode;

    public function __construct()
    {
        $this->logMode = Str::lower(Plugin::getLogMode());

        if ($this->logMode === 'single' ||
            $this->logMode === 'failed') {
            $this->emptyLog();
        }
    }

    protected function emptyLog(): void
    {
        file_put_contents($this->getFilePath(), '');
    }

    public function logCrawl(bool $isError, string $url, int $statusCode, ?string $foundOnUrl = null): void
    {
        $prefix = $isError ? '[FAILED]' : '[CRAWLED]';
        $message = "{$prefix}:({$statusCode}) {$url}";
        $this->writeLine($message);
    }

    public function writeLine(string $line): void
    {
        file_put_contents($this->getFilePath(), $line . PHP_EOL, FILE_APPEND);
    }

    protected function getFilePath(): string
    {
        return storage_path(Plugin::getLogFile());
    }
}
