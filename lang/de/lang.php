<?php

return [
    'plugin' => [
        'name' => 'Spider',
        'description' => 'Crawler für OctoberCMS',
    ],
    'overview' => [
        'title' => 'Übersicht',
        'no_data' => 'Keine Daten verfügbar',
        'columns' => [
            'status_code' => 'Status Code',
            'url' => 'URL',
        ],
        'filters' => [
            'all' => 'Alle',
            'errors' => 'Fehler'
        ],
        'run_scheduled' => 'Ein Crawl wurde in die Queue eingereiht',
        'scheduling' => 'Starten...',
    ],
    'permissions' => [
        'tab' => 'Spider',
        'label' => 'Zugriff auf Backend-Übersicht',
    ],
];
