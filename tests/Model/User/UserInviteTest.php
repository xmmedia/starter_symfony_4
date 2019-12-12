<?php

declare(strict_types=1);

namespace App\Tests\Model\User;

use App\Model\User\Event;
use App\Model\User\Exception;
use App\Model\User\Token;
use App\Tests\BaseTestCase;
use Xm\SymfonyBundle\Model\EmailGatewayMessageId;

class UserInviteTest extends BaseTestCase
{
    use UserTestTrait;

    public function testInviteSent(): void
    {
        $faker = $this->faker();

        $user = $this->getUserActive(true);

        $token = Token::fromString($faker->asciify(str_repeat('*', 25)));
        $messageId = EmailGatewayMessageId::fromString($faker->uuid);

        $user->inviteSent($token, $messageId);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\InviteSent::class,
            [
                'token'     => $token->toString(),
                'messageId' => $messageId->toString(),
            ],
            $events
        );

        $this->assertCount(1, $events);
    }

    public function testInviteSentAlreadyVerified(): void
    {
        $faker = $this->faker();
        $token = Token::fromString($faker->asciify(str_repeat('*', 25)));
        $messageId = EmailGatewayMessageId::fromString($faker->uuid);

        $user = $this->getUserActive();

        $this->expectException(Exception\UserAlreadyVerified::class);

        $user->inviteSent($token, $messageId);
    }
}
