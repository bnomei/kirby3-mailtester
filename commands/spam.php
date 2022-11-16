<?php

use Bnomei\Janitor;
use Kirby\CLI\CLI;
use Kirby\Http\Remote;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

return [
    'description' => 'mail-tester.com Spam rating for email send from a page',
    'args' => [
            'username' => [
                'longPrefix' => 'username',
                'description' => 'mail-tester.com username',
                'defaultValue' => '', // try .env
                'castTo' => 'string',
            ],
            'server' => [
                'longPrefix' => 'server',
                'description' => 'mail-tester.com email server',
                'defaultValue' => '', // try .env USERNAME@srv1.mail-tester.com
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
        $username = $cli->arg('username');
        if (empty($username)) {
            $username = getenv('MAILTESTER_USERNAME');
        }

        $server = $cli->arg('server');
        if (empty($server)) {
            $server = getenv('MAILTESTER_SERVER');
        }
        if (empty($server) || $server === false) {
            $server = 'srv1.mail-tester.com';
        }

        // from, subject, body[text,html], transport
        $emailData = $cli->arg('data');
        $spamid = Str::slug($username . '-' . md5($emailData . time()));
        $emailData = empty($emailData) ? [] : json_decode($emailData, true);

        defined('STDOUT') && $cli->out('Sending email to mail-tester.com...');
        $success = $cli->kirby()->email(
            $emailData + [
                'to' => $username . '@' . $server,
            ]
        )->isSent();

        if ($success) {
            defined('STDOUT') && $cli->green('Email send.');
            $limit = intval($cli->arg('limit'));
            $message = t('mailtester.timeout', 'Timeout');
            $href = null;
            $found = false;
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
                    $limit = 0;
                    $message = $json['displayedMark'];
                    $found = true;
                    if (abs($json['mark']) > 1.5) { // link if rating is BAD
                        $href = 'https://www.mail-tester.com/' . $spamid;
                    }
                    defined('STDOUT') && $cli->green('Recieved a spam report.');
                } else {
                    $limit--;
                }
            }

            $data = [
                'status' => $found ? 200 : 204,
                'message' => $message,
            ];
            if ($href) {
                $data = $data + [
                        'message' => $message . '...',
                        'href' => $href,
                    ];
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
