<?php

Kirby::plugin('tests/email', [
    'siteMethods' => [
        'emailDataJSON' => function (): array {
            return json_decode(F::read(__DIR__ . '/../../fixtures/email.json'));
        }
    ]
]);
