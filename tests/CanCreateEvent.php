<?php

declare(strict_types=1);

namespace App\Tests;

use App\Messaging\DomainEvent;
use Faker;

trait CanCreateEvent
{
    protected function createEvent(
        string $eventName,
        string $aggregateId,
        array $payload = []
    ): DomainEvent {
        $faker = Faker\Factory::create();

        return $eventName::fromArray([
            'message_name' => $eventName,
            'uuid'         => $faker->uuid,
            'payload'      => $payload,
            'metadata' => [
                '_aggregate_id' => $aggregateId,
            ],
            'created_at' => new \DateTimeImmutable(),
        ]);
    }
}
