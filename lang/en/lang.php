<?php

return [
    'plugin' => [
        'name' => 'Spider',
        'description' => 'Crawler for OctoberCMS',
    ],
    'overview' => [
        'title' => 'Overview',
        'no_data' => 'No data available',
        'columns' => [
            'status_code' => 'Status Code',
            'url' => 'URL',
        ],
        'filters' => [
            'all' => 'All',
            'errors' => 'Errors'
        ],
        'run_scheduled' => 'A crawl has been queued',
        'scheduling' => 'Scheduling...',
        'run_url_unreachable' => 'Site URL :url is not reachable from the server, the crawl may fail',
        'schedule_run' => 'Schedule run',
    ],
    'permissions' => [
        'tab' => 'Spider',
        'label' => 'Access to backend overview',
    ],
];
