<?php

declare(strict_types=1);

namespace App\Tests\Messenger;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Bridge\Doctrine\Transport\Connection;
use Symfony\Component\Yaml\Yaml;

/**
 * The transports in config/packages/messenger.yaml, with MESSENGER_TRANSPORT_DSN from .env, as the Doctrine transport
 * reads them. A query string appended to a DSN that already has one doesn't add an option, so failed messages would go
 * back to the async queue.
 */
class MessengerTransportsTest extends TestCase
{
    private const string CONFIG = __DIR__.'/../../config/packages/messenger.yaml';

    public function testFailedMessagesHaveTheirOwnQueue(): void
    {
        $async = $this->transportConfiguration('async');
        $failed = $this->transportConfiguration('failed');

        $this->assertSame('failed', $failed['queue_name']);
        $this->assertNotSame($async['queue_name'], $failed['queue_name']);
        $this->assertSame($async['auto_setup'], $failed['auto_setup']);
    }

    /**
     * The messenger:failed: commands use the default failure transport.
     */
    public function testFailedIsTheDefaultFailureTransport(): void
    {
        $this->assertSame('failed', $this->messengerConfig()['failure_transport'] ?? null);
    }

    private function transportConfiguration(string $name): array
    {
        $transport = $this->messengerConfig()['transports'][$name];
        if (\is_string($transport)) {
            $transport = ['dsn' => $transport];
        }

        return Connection::buildConfiguration(
            str_replace('%env(MESSENGER_TRANSPORT_DSN)%', $_SERVER['MESSENGER_TRANSPORT_DSN'], $transport['dsn']),
            $transport['options'] ?? [],
        );
    }

    private function messengerConfig(): array
    {
        return Yaml::parseFile(self::CONFIG)['framework']['messenger'];
    }
}
