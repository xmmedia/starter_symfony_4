<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Event;

use App\Model\User\Event\PasswordRecoverySent;
use App\Model\User\Token;
use App\Tests\BaseTestCase;
use Xm\SymfonyBundle\Model\EmailGatewayMessageId;
use Xm\SymfonyBundle\Tests\CanCreateEventFromArray;

class PasswordRecoverySentTest extends BaseTestCase
{
    use CanCreateEventFromArray;

    public function testOccur(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $token = Token::fromString($faker->asciify('token'));
        $messageId = EmailGatewayMessageId::fromString($faker->uuid);

        $event = PasswordRecoverySent::now($userId, $token, $messageId);

        $this->assertEquals($userId, $event->userId());
        $this->assertEquals($token, $event->token());
        $this->assertEquals($messageId, $event->messageId());
    }

    public function testFromArray(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $token = Token::fromString($faker->asciify('token'));
        $messageId = EmailGatewayMessageId::fromString($faker->uuid);

        /** @var PasswordRecoverySent $event */
        $event = $this->createEventFromArray(
            PasswordRecoverySent::class,
            $userId->toString(),
            [
                'token'     => $token->toString(),
                'messageId' => $messageId->toString(),
            ]
        );

        $this->assertInstanceOf(PasswordRecoverySent::class, $event);

        $this->assertEquals($userId, $event->userId());
        $this->assertEquals($token, $event->token());
        $this->assertEquals($messageId, $event->messageId());
    }
}
