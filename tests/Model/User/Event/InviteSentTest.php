<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Event;

use App\Model\User\Event\InviteSent;
use App\Tests\BaseTestCase;
use Xm\SymfonyBundle\Model\EmailGatewayMessageId;
use Xm\SymfonyBundle\Tests\CanCreateEventFromArray;

class InviteSentTest extends BaseTestCase
{
    use CanCreateEventFromArray;

    public function testOccur(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();
        $messageId = EmailGatewayMessageId::fromString($faker->uuid());

        $event = InviteSent::now($userId, $messageId);

        $this->assertEquals($userId, $event->userId());
        $this->assertEquals($messageId, $event->messageId());
    }

    public function testFromArray(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();
        $messageId = EmailGatewayMessageId::fromString($faker->uuid());

        /** @var InviteSent $event */
        $event = $this->createEventFromArray(
            InviteSent::class,
            $userId->toString(),
            [
                'messageId' => $messageId->toString(),
            ],
        );

        $this->assertInstanceOf(InviteSent::class, $event);

        $this->assertEquals($userId, $event->userId());
        $this->assertEquals($messageId, $event->messageId());
    }
}
