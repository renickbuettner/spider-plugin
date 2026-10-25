<?php namespace Renick\Spider\Tests;

use PluginTestCase;

/**
 * Base class: the plugin dir is symlinked into the app, so the plugin code
 * can't be guessed from the test file path. No database is needed.
 */
abstract class SpiderTestCase extends PluginTestCase
{
    protected $autoMigrate = false;

    /** env vars touched by a test, restored in tearDown */
    protected array $touchedEnv = [];

    protected function guessPluginCodeFromTest()
    {
        return 'Renick.Spider';
    }

    protected function setEnv(string $key, ?string $value): void
    {
        if (!array_key_exists($key, $this->touchedEnv)) {
            $old = getenv($key);
            $this->touchedEnv[$key] = $old === false ? null : $old;
        }
        $this->applyEnv($key, $value);
    }

    private function applyEnv(string $key, ?string $value): void
    {
        if ($value === null) {
            putenv($key);
            unset($_ENV[$key], $_SERVER[$key]);
        } else {
            putenv("$key=$value");
            $_ENV[$key] = $_SERVER[$key] = $value;
        }
    }

    public function tearDown(): void
    {
        foreach ($this->touchedEnv as $key => $value) {
            $this->applyEnv($key, $value);
        }
        $this->touchedEnv = [];
        parent::tearDown();
    }
}
