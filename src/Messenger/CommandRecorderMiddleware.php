<?php

declare(strict_types=1);

namespace App\Messenger;

use App\Messaging\Command;
use Doctrine\DBAL\Connection;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

class CommandRecorderMiddleware implements MiddlewareInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();

        if ($message instanceof Command) {
            $this->record($message);
        }

        return $stack->next()->handle($envelope, $stack);
    }

    public function record(Command $command): void
    {
        $this->connection->insert('command_log', [
            'command_id' => $command->uuid()->toString(),
            'command'    => get_class($command),
            'payload'    => $command->payload(),
            'metadata'   => $command->metadata(),
            'sent_at'    => $command->createdAt(),
        ], [
            'payload'  => 'json',
            'metadata' => 'json',
            'sent_at'  => 'datetime',
        ]);
    }
}
