<?php

declare(strict_types=1);

namespace App\Messaging;

use DateTimeImmutable;
use Webmozart\Assert\Assert;

/**
 * @see https://github.com/prooph/common
 */
final class MessageDataAssertion
{
    /**
     * @param mixed $messageData
     */
    public static function assert($messageData): void
    {
        Assert::isArray($messageData, 'MessageData must be an array');
        Assert::keyExists($messageData, 'message_name', 'MessageData must contain a key message_name');
        Assert::keyExists($messageData, 'uuid', 'MessageData must contain a key uuid');
        Assert::keyExists($messageData, 'payload', 'MessageData must contain a key payload');
        Assert::keyExists($messageData, 'metadata', 'MessageData must contain a key metadata');
        Assert::keyExists($messageData, 'created_at', 'MessageData must contain a key created_at');

        self::assertMessageName($messageData['message_name']);
        self::assertUuid($messageData['uuid']);
        self::assertPayload($messageData['payload']);
        self::assertMetadata($messageData['metadata']);
        self::assertCreatedAt($messageData['created_at']);
    }

    public static function assertUuid($uuid): void
    {
        Assert::uuid($uuid, 'uuid must be a valid UUID string');
    }

    public static function assertMessageName($messageName): void
    {
        Assert::minLength($messageName, 3, 'message_name must be string with at least 3 chars length');
    }

    public static function assertPayload($payload): void
    {
        Assert::isArray($payload, 'payload must be an array');
        self::assertSubPayload($payload);
    }

    /**
     * @param mixed $payload
     */
    private static function assertSubPayload($payload): void
    {
        if (\is_array($payload)) {
            foreach ($payload as $subPayload) {
                self::assertSubPayload($subPayload);
            }

            return;
        }

        Assert::nullOrScalar($payload, 'payload must only contain arrays and scalar values');
    }

    public static function assertMetadata($metadata): void
    {
        Assert::isArray($metadata, 'metadata must be an array');

        foreach ($metadata as $key => $value) {
            Assert::minLength($key, 1, 'A metadata key must be non empty string');
            Assert::scalar($value, 'A metadata value must have a scalar type. Got '.\gettype($value).' for '.$key);
        }
    }

    public static function assertCreatedAt($createdAt): void
    {
        Assert::isInstanceOf($createdAt, DateTimeImmutable::class, \sprintf(
            'created_at must be of type %s. Got %s',
            DateTimeImmutable::class,
            \is_object($createdAt) ? \get_class($createdAt) : \gettype($createdAt)
        ));
    }
}
