<?php

declare(strict_types=1);

namespace App\Tests\Messenger;

use App\Tests\UsesFaker;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Bridge\Doctrine\Transport\DoctrineTransport;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;
use Symfony\Component\Messenger\Stamp\SentToFailureTransportStamp;

/**
 * The transports as the app builds them, with MESSENGER_TRANSPORT_DSN from .env. A query string appended to a DSN
 * that already has one doesn't add an option, & a queue_name in the DSN overrides the option, so either would send
 * failed messages back to the async queue.
 */
class MessengerTransportsTest extends KernelTestCase
{
    use UsesFaker;

    private const int MAX_RETRIES = 3;

    protected function setUp(): void
    {
        self::bootKernel();

        // auto_setup is off, so the table isn't created on the first send
        $this->transport('async')->setup();
        $this->clear();
    }

    protected function tearDown(): void
    {
        $this->clear();

        parent::tearDown();
    }

    public function testMessageFailingOnAsyncGoesToTheFailedQueue(): void
    {
        $this->failOnAsync($this->faker()->sentence());

        $this->assertSame(0, $this->transport('async')->getMessageCount());

        $failed = iterator_to_array($this->transport('failed')->all());
        $this->assertCount(1, $failed);
        $this->assertSame('async', $failed[0]->last(SentToFailureTransportStamp::class)?->getOriginalReceiverName());
    }

    public function testFailedMessagesAreListedByFailedShow(): void
    {
        $this->failOnAsync($this->faker()->sentence());

        $tester = new CommandTester(new Application(self::$kernel)->find('messenger:failed:show'));
        $tester->execute([]);

        $tester->assertCommandIsSuccessful();
        $this->assertStringContainsString('There is 1 message pending in the failure transport', $tester->getDisplay());
        $this->assertStringContainsString(\stdClass::class, $tester->getDisplay());
    }

    /**
     * Simulates the worker failing a message on its last retry.
     */
    private function failOnAsync(string $text): void
    {
        $message = new \stdClass();
        $message->text = $text;

        $async = $this->transport('async');
        $async->send(new Envelope($message, [new RedeliveryStamp(self::MAX_RETRIES)]));

        $envelope = iterator_to_array($async->get())[0];

        self::getContainer()->get(EventDispatcherInterface::class)->dispatch(
            new WorkerMessageFailedEvent($envelope, 'async', new \RuntimeException($text)),
        );
        $async->reject($envelope);
    }

    /**
     * Deletes every row, including ones reserved by get(), which all() doesn't return.
     */
    private function clear(): void
    {
        self::getContainer()->get(Connection::class)->executeStatement('DELETE FROM messenger_messages');
    }

    private function transport(string $name): DoctrineTransport
    {
        return self::getContainer()->get('messenger.transport.'.$name);
    }
}
