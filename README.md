# Renick.Spider

Just a simple OctoberCMS plugin for crawling your local installation.
Laravel 9+ is required, see [Compatibility](#compatibility).

- [x] crawl all pages to warmup your cache and resizer images
- [x] find 404 not found errors in log file
- [x] OctoberCMS MultiSite support
- [x] Reset all caches before crawling

## Compatibility

Requires PHP 8.1+ (limited by `spatie/crawler` 8.x; newer crawler releases need a newer PHP) and Laravel 9+ (the
Laravel HTTP client with Guzzle 7 is used to probe the base URL). The plugin is not installable on OctoberCMS 1.x/2.x
(Laravel 5.5/6), and works with OctoberCMS 3.x and 4.x:

| OctoberCMS | Laravel | PHP (core) |
|------------|---------|------------|
| 1.0        | 5.5     | 7.0+       |
| 1.1 / 2.x  | 6       | 7.2+       |
| 3.0 - 3.7  | 9 / 10  | 8.0.2+     |
| 4.x        | 12      | 8.1+       |

Sources: `composer.json` of `octobercms/library` and the OctoberCMS documentation.

To install this plugin, you can just run the following command:
```bash
php artisan plugin:install Renick.Spider
```
or
```bash
php artisan plugin:install Renick.Spider --from=git@github.com:renickbuettner/spider-plugin.git --want=dev-main
```

---

We have to modes the plug-in can be used at the moment. We try to keep our plug-ins really basic.
You can use the command-line tool, or the cronjob.

```bash
php artisan spider:crawl
```

You can set up the behavior of this plug-in heavily with environment variables. Here are the options:

```dotenv
SPIDER_CRON_ENABLED=true

SPIDER_LOG_MODE=single|append|failed # single: last run only, append: keep history, failed: last run, errors only
SPIDER_LOG_FILE=logs/spider.log
SPIDER_LOG_MAX_SIZE=5242880 # bytes; in append mode the log is rotated to spider.log.1 above this size

SPIDER_CONCURRENCY=10
SPIDER_CACHE_CLEAR=true
SPIDER_CRON_SCHEDULE="1 1 * * *"
SPIDER_CACHE_CLEAR_RESIZER=false
SPIDER_USER_AGENT="Mozilla/5.0 (compatible; Renick.Spider/1.0;)"
```

### Cloudflare adjustments

If you use Cloudflare, you should set up a Cache Rule to bypass the cache for the user agent contains `Renick.Spider`.

---


Feel free to ask, raise ideas, mind bugs or contribute on the Github repository. Please create a new issue or pull
request.
