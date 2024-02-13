<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Event;

use App\Model\User\Event\PasswordRecoverySent;
use App\Tests\BaseTestCase;
use Xm\SymfonyBundle\Model\EmailGatewayMessageId;
use Xm\SymfonyBundle\Tests\CanCreateEventFromArray;

class PasswordRecoverySentTest extends BaseTestCase
{
    use CanCreateEventFromArray;

    public function testOccur(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();
        $messageId = EmailGatewayMessageId::fromString($faker->uuid());

        $event = PasswordRecoverySent::now($userId, $messageId);

        $this->assertEquals($userId, $event->userId());
        $this->assertEquals($messageId, $event->messageId());
    }

    public function testFromArray(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();
        $messageId = EmailGatewayMessageId::fromString($faker->uuid());

        /** @var PasswordRecoverySent $event */
        $event = $this->createEventFromArray(
            PasswordRecoverySent::class,
            $userId->toString(),
            [
                'messageId' => $messageId->toString(),
            ],
        );

        $this->assertInstanceOf(PasswordRecoverySent::class, $event);

        $this->assertEquals($userId, $event->userId());
        $this->assertEquals($messageId, $event->messageId());
    }
}
