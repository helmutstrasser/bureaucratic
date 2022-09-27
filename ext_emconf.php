<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'bureaucratic',
    'description' => 'Basics for professional TYPO3 projects',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-12.5.99',
        ],
    ],
    'version' => '1.0.0',
    'state' => 'stable',
    'autoload' => [
        'psr-4' => [
            'Josefglatz\\Bureaucratic\\' => 'Classes/',
        ],
    ],
    'author' => 'Josef Glatz',
    'author_email' => 'typo3@josefglatz.at',
    'author_company' => 'J18',
];
