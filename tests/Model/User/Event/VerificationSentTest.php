<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Event;

use App\Model\User\Event\VerificationSent;
use App\Tests\BaseTestCase;
use Xm\SymfonyBundle\Model\EmailGatewayMessageId;
use Xm\SymfonyBundle\Model\NotificationGatewayId;
use Xm\SymfonyBundle\Tests\CanCreateEventFromArray;

class VerificationSentTest extends BaseTestCase
{
    use CanCreateEventFromArray;

    public function testOccur(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();
        $messageId = NotificationGatewayId::fromString($faker->uuid());

        $event = VerificationSent::now($userId, $messageId);

        $this->assertEquals($userId, $event->userId());
        $this->assertEquals($messageId, $event->messageId());
    }

    public function testFromArray(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();
        $messageId = EmailGatewayMessageId::fromString($faker->uuid());

        /** @var VerificationSent $event */
        $event = $this->createEventFromArray(
            VerificationSent::class,
            $userId->toString(),
            [
                'messageId' => $messageId->toString(),
            ],
        );

        $this->assertInstanceOf(VerificationSent::class, $event);

        $this->assertEquals($userId, $event->userId());
        $this->assertEquals($messageId, $event->messageId());
    }
}
