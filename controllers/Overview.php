<?php namespace Renick\Spider\Controllers;

use Artisan;
use BackendMenu;
use Backend\Classes\Controller;
use Flash;
use Renick\Spider\Classes\SiteCrawler;
use Renick\Spider\Classes\SiteCrawlParser;
use Site;
use SystemException;

/**
 * Overview Backend Controller
 *
 * @link https://docs.octobercms.com/3.x/extend/system/controllers.html
 */
class Overview extends Controller
{
    public $implement = [];

    /**
     * @var array required permissions
     */
    public $requiredPermissions = ['renick.spider.overview'];

    /**
     * __construct the controller
     */
    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Renick.Spider', 'spider', 'overview');
    }

    /**
     * @throws SystemException
     */
    public function index()
    {
        $this->bodyClass = 'compact-container';
        $this->pageTitle = trans('renick.spider::lang.overview.title'). " | Spider";
        $filter = input('filter') ?? 'all';
        $parser = new SiteCrawlParser();

        $this->vars['data'] = collect($parser->parse())
            ->filter(fn ($row) => $filter !== 'errors' || intval($row['status_code']) >= 400)
            ->toArray();

        $this->vars['date'] = $parser->getDate();

        return $this->makePartial('overview');
    }

    public function onScheduleRun()
    {
        $warned = false;
        foreach (Site::listEnabled() as $site) {
            if (!(new SiteCrawler($site))->isValid()) {
                $warned = true;
                Flash::warning(trans('renick.spider::lang.overview.run_url_unreachable', ['url' => (string) $site->base_url]));
            }
        }

        if (config('queue.default') === 'sync') {
            dispatch(fn () => Artisan::call('spider:crawl'))->afterResponse();
        } else {
            Artisan::queue('spider:crawl');
        }
        $warned || Flash::info(trans('renick.spider::lang.overview.run_scheduled'));
    }
}
