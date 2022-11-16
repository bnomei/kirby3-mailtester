<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Kirby\Filesystem\F;
use PHPUnit\Framework\TestCase;

final class JanitorTest extends TestCase
{
    public function testCommand()
    {
        $this->markTestSkipped('needs email sending capabilities');

        $data = F::read(__DIR__ . '/fixtures/email.json');
        $this->assertEquals(
            200,
            janitor()->command('mailtester:spam --data ' . $data)['status']
        );
    }
}
