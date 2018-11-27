<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Event;

use App\Model\EmailGatewayMessageId;
use App\Model\User\Event\PasswordRecoverySent;
use App\Model\User\Token;
use App\Model\User\UserId;
use App\Tests\CanCreateEventFromArray;
use Faker;
use PHPUnit\Framework\TestCase;

class PasswordRecoverySentTest extends TestCase
{
    use CanCreateEventFromArray;

    public function testOccur(): void
    {
        $faker = Faker\Factory::create();

        $userId = UserId::generate();
        $token = Token::fromString($faker->asciify('token'));
        $messageId = EmailGatewayMessageId::fromString($faker->uuid);

        $event = PasswordRecoverySent::now($userId, $token, $messageId);

        $this->assertEquals($userId, $event->userId());
        $this->assertEquals($token, $event->token());
        $this->assertEquals($messageId, $event->messageId());
    }

    public function testFromArray(): void
    {
        $faker = Faker\Factory::create();

        $userId = UserId::generate();
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
