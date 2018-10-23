<?php

declare(strict_types=1);

namespace App\Commanding;

use DateTimeImmutable;
use Ramsey\Uuid\UuidInterface;

/**
 * @see https://github.com/prooph/common
 */
interface Message extends HasMessageName
{
    public const TYPE_COMMAND = 'command';
    public const TYPE_EVENT = 'event';

    /**
     * Should be one of Message::TYPE_COMMAND, Message::TYPE_EVENT or Message::TYPE_QUERY.
     */
    public function messageType(): string;

    public function uuid(): UuidInterface;

    public function createdAt(): DateTimeImmutable;

    public function payload(): array;

    public function metadata(): array;

    public function withMetadata(array $metadata): Message;

    /**
     * Returns new instance of message with $key => $value added to metadata.
     *
     * Given value must have a scalar or array type.
     */
    public function withAddedMetadata(string $key, $value): Message;
}
