# Renick.Spider

Just a simple OctoberCMS plugin for crawling your local installation.

- [x] crawl all pages to warmup your cache and resizer images
- [x] find 404 not found errors in log file
- [x] OctoberCMS MultiSite support
- [x] Reset all caches before crawling

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

SPIDER_LOG_MODE=single|append|failed
SPIDER_LOG_FILE=logs/spider.log

SPIDER_CONCURRENCY=10
SPIDER_CACHE_CLEAR=true
SPIDER_CACHE_CLEAR_RESIZER=false
SPIDER_USER_AGENT="Mozilla/5.0 (compatible; Renick.Spider/1.0;)"
```

---


Feel free to ask, raise ideas, mind bugs or contribute on the Github repository. Please create a new issue or pull
request.
