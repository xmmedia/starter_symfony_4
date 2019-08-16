<?php

declare(strict_types=1);

namespace App\Messaging;

use Prooph\Common\Messaging\DomainMessage as BaseDomainMessage;
use Prooph\Common\Messaging\Message as BaseMessage;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

/**
 * Base class for commands and domain events.
 * All are messages but differ in their intention.
 */
abstract class DomainMessage extends BaseDomainMessage implements Message
{
    /**
     * @var string
     */
    protected $messageName;

    /**
     * @var UuidInterface
     */
    protected $uuid;

    /**
     * @var \DateTimeImmutable
     */
    protected $createdAt;

    /**
     * @var array
     */
    protected $metadata = [];

    abstract protected function setPayload(array $payload): void;

    public static function fromArray(array $messageData): BaseDomainMessage
    {
        MessageDataAssertion::assert($messageData);

        $messageRef = new \ReflectionClass(\get_called_class());

        /** @var $message DomainMessage */
        $message = $messageRef->newInstanceWithoutConstructor();

        $message->uuid = Uuid::fromString($messageData['uuid']);
        $message->messageName = $messageData['message_name'];
        $message->metadata = $messageData['metadata'];
        $message->createdAt = $messageData['created_at'];
        $message->setPayload($messageData['payload']);

        return $message;
    }

    protected function init(): void
    {
        if (null === $this->uuid) {
            $this->uuid = Uuid::uuid4();
        }

        if (null === $this->messageName) {
            $this->messageName = \get_class($this);
        }

        if (null === $this->createdAt) {
            $this->createdAt = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        }
    }

    public function uuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function metadata(): array
    {
        return $this->metadata;
    }

    public function toArray(): array
    {
        return [
            'message_name' => $this->messageName,
            'uuid'         => $this->uuid->toString(),
            'payload'      => $this->payload(),
            'metadata'     => $this->metadata,
            'created_at'   => $this->createdAt(),
        ];
    }

    public function messageName(): string
    {
        return $this->messageName;
    }

    public function withMetadata(array $metadata): BaseMessage
    {
        $message = clone $this;

        $message->metadata = $metadata;

        return $message;
    }

    /**
     * Returns new instance of message with $key => $value added to metadata.
     *
     * Given value must have a scalar type.
     */
    public function withAddedMetadata(string $key, $value): BaseMessage
    {
        Assert::notEmpty($key, 'Invalid key');

        $message = clone $this;

        $message->metadata[$key] = $value;

        return $message;
    }
}
