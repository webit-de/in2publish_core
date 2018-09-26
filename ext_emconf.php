<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'in2publish Core',
    'description' => 'Content publishing extension to connect stage and production server',
    'category' => 'plugin',
    'version' => '8.0.0-dev',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearcacheonload' => 1,
    'author' => 'Alex Kellner, Oliver Eglseder, Thomas Scheibitz',
    'author_email' => 'alexander.kellner@in2code.de, oliver.eglseder@in2code.de, thomas.scheibitz@in2code.de',
    'author_company' => 'in2code.de',
    'constraints' => [
        'depends' => [
            'typo3' => '8.4.0-9.99.99',
            'php' => '7.0.0-7.2.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
