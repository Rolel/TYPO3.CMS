<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'Web>Info, Page TSconfig',
    'description' => 'Displays the compiled Page TSconfig values relative to a page.',
    'category' => 'module',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'author' => 'Kasper Skaarhoj',
    'author_email' => 'kasperYYYY@typo3.com',
    'author_company' => 'Curby Soft Multimedia',
    'version' => '8.6.0',
    'constraints' => [
        'depends' => [
            'typo3' => '8.6.0-8.6.99',
            'info' => '8.6.0-8.6.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
