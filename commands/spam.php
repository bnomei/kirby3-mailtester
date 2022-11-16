<?php

use Bnomei\Janitor;
use Kirby\CLI\CLI;
use Kirby\Http\Remote;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;
use Kirby\Uuid\Uuid;

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
                'defaultValue' => 5,
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
            $to = getenv('MAILTESTER_USERNAME');
        }

        $spamid = explode('@', $to)[0];
        if (!Str::contains($spamid, '-')) {
            $spamid .= '-' . Uuid::generate(9);
        }

        // from, subject, body[text,html], transport
        $emailData = $cli->arg('data');
        $emailData = empty($emailData) ? [] : json_decode($emailData, true);

        defined('STDOUT') && $cli->out('Sending email to <' .$to . '> ...');

        $success = $cli->kirby()->email(
            $emailData + [
                'to' => $to,
            ]
        )->isSent();

        if ($success) {
            defined('STDOUT') && $cli->green('Email send.');
            defined('STDOUT') && $cli->blue('Report URL: https://www.mail-tester.com/' . $spamid);

            $limit = intval($cli->arg('limit'));
            $message = t('mailtester.timeout', 'Timeout');
            $mark = 0;
            while ($limit > 0) {
                defined('STDOUT') && $cli->out('Waiting for response from mail-tester.com...');

                sleep(intval($cli->arg('wait')));

                $result = Remote::get(implode([
                    'https://www.mail-tester.com/',
                    $spamid,
                    '&format=json',
                ]));
                $json = $result->json();
                if ($json['messageId'] !== 0) {
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
                'message' => $message,
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

        // output for the command line
        defined('STDOUT') && $href && $cli->blue($href);
        defined('STDOUT') && (
            $found ? $cli->success($message) : $cli->error($message)
        );

        // output for janitor
        janitor()->data($cli->arg('command'), $data);
    }
];
