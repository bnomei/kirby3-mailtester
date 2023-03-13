<?php

use Bnomei\Janitor;
use Kirby\CLI\CLI;
use Kirby\Filesystem\F;
use Kirby\Http\Remote;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;
use Kirby\Uuid\Uuid;

if (!class_exists('Bnomei\Janitor')) {
    foreach ([
                 __DIR__ . '/../kirby3-janitor/classes/Janitor.php',
                 __DIR__ . '/../tests/site/plugins/kirby3-janitor/classes/Janitor.php',
             ] as $file) {
        F::exists($file) && require_once $file;
    }
}

return [
    'description' => 'mail-tester.com Spam rating for email',
    'args' => [
            'to' => [
                'longPrefix' => 'to',
                'description' => 'mail-tester.com email',
                'defaultValue' => '', // try .env
                'castTo' => 'string',
            ],
            'limit' => [
                'longPrefix' => 'limit',
                'description' => 'get email spam report requests limit',
                'defaultValue' => 10,
                'castTo' => 'int',
            ],
            'wait' => [
                'longPrefix' => 'wait',
                'description' => 'wait duration in seconds per request',
                'defaultValue' => 3,
                'castTo' => 'int',
            ],
        ] + Janitor::ARGS, // page, file, user, site, data
    'command' => static function (CLI $cli): void {
        $to = $cli->arg('to');
        if (empty($to)) {
            $to = $cli->kirby()->site()->getenv('MAILTESTER_USERNAME');
        }

        $spamid = explode('@', $to)[0];
        if (!Str::contains($spamid, '-')) {
            $spamid = $spamid . '-' . Uuid::generate(9);
        }

        // from, subject, body[text,html], transport
        $emailData = $cli->arg('data');
        $emailData = empty($emailData) ? [] : json_decode($emailData, true);

        defined('STDOUT') && $cli->out('Sending email to <' . $to . '> ...');

        $success = $cli->kirby()->email(
            $emailData + [
                'to' => $spamid . '@srv1.mail-tester.com',
            ],
        )->isSent();

        try {
            if ($success) {
                defined('STDOUT') && $cli->green('Email send.');
                defined('STDOUT') && $cli->blue('Report URL: https://www.mail-tester.com/' . $spamid);

                $limit = intval($cli->arg('limit'));
                $message = t('mailtester.timeout', 'Timeout');
                $mark = 0;
                while ($limit > 0) {
                    defined('STDOUT') && $cli->out('Waiting for response from mail-tester.com...');

                    sleep(intval($cli->arg('wait')));

                    // can set longer timeout if needed
                    // https://getkirby.com/docs/reference/system/options/remote
                    $result = Remote::get(implode([
                        'https://www.mail-tester.com/',
                        $spamid,
                        '&format=json',
                    ]));

                    $json = $result->json();
                    $messageId = A::get($json, 'messageId');
                    if ($messageId && $messageId !== 0) {
                        defined('STDOUT') && $cli->green('Received a spam report.');

                        $limit = 0; // stop
                        $message = $json['displayedMark'];
                        $mark = abs($json['mark']);
                    } else {
                        $limit--;
                    }
                }

                $data = [
                    'status' => $mark > 0 ? 200 : 204,
                    'label' => $message,
                    'backgroundColor' => 'var(--color-positive)',
                    'icon' => 'check',
                    'mark' => $mark,
                    'report' => 'https://www.mail-tester.com/' . $spamid,
                ];

                if ($mark > 1.5) { // open link in panel if rating is BAD
                    $data['open'] = $data['report'];
                }
            } else {
                $data = [
                    'status' => 500,
                    'message' => t('mailtester.sending-email-failed', 'Sending email failed'),
                ];
            }
        } catch (\Exception $exception) {
            $data = [
                'status' => 500,
                'message' => $exception->getMessage(),
            ];
        }

        // output for the command line
        defined('STDOUT') && $cli->blue($data['report']);
        defined('STDOUT') && (
            $mark > 0 ? $cli->success($message) : $cli->error($message)
        );

        // output for janitor
        janitor()->data($cli->arg('command'), $data);
    }
];
