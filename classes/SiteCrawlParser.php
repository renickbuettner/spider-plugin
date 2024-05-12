<?php namespace Renick\Spider\Classes;

use Carbon\Carbon;
use Renick\Spider\Plugin;

class SiteCrawlParser
{
    public string $filePath;

    public function __construct()
    {
        $this->filePath = storage_path(Plugin::getLogFile());
    }

    public function parse(): array
    {
        if (!file_exists($this->filePath)) return [];

        $file = fopen($this->filePath, 'r');
        $data = [];

        while (($line = fgets($file)) !== false) {
            if (is_string($line) && !empty($line)) {
                $data[] = $this->parseLine($line);
            }
        }

        fclose($file);
        return $data;
    }

    protected function parseLine(string $line): array
    {
        try {
            $matches = [];
            preg_match('/\[\w+\]:\((\d{3})\) (([\/\.\d\w\:\-]+))/im', $line, $matches);
            return [
                'status_code' => $matches[1] ?? -1,
                'url' => $matches[2] ?? '-'
            ];

        } catch (\Exception $e) {
            return [];
        }
    }

    public function getDate(): ?Carbon
    {
        if (!file_exists($this->filePath)) return null;

        return new Carbon(filemtime($this->filePath));
    }
}
