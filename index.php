<?php

@include_once __DIR__ . '/vendor/autoload.php';

Kirby::plugin('bnomei/mailtester', [
    'options' => [

    ],
    'commands' => [ // https://github.com/getkirby/cli
        'mailtester:spam' => require __DIR__ . '/commands/spam.php',
    ],
    'translations' => [
        'en' => [
            // defined inline as fallbacks
        ],
        'de' => [
            'mailtester.timeout' => 'Timeout-Fehler',
            'mailtester.sending-email-failed' => 'Email konnte nicht versandt werden',
        ],
    ]
]);
