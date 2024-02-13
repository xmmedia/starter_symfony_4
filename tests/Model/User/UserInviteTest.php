<?php

declare(strict_types=1);

namespace App\Tests\Model\User;

use App\Model\User\Event;
use App\Model\User\Exception;
use App\Tests\BaseTestCase;
use Xm\SymfonyBundle\Model\EmailGatewayMessageId;

class UserInviteTest extends BaseTestCase
{
    use UserTestTrait;

    public function testInviteSent(): void
    {
        $faker = $this->faker();

        $user = $this->getUserActive(true);

        $messageId = EmailGatewayMessageId::fromString($faker->uuid());

        $user->inviteSent($messageId);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\InviteSent::class,
            [
                'messageId' => $messageId->toString(),
            ],
            $events,
        );

        $this->assertCount(1, $events);
    }

    public function testInviteSentAlreadyVerified(): void
    {
        $faker = $this->faker();
        $messageId = EmailGatewayMessageId::fromString($faker->uuid());

        $user = $this->getUserActive();

        $this->expectException(Exception\UserAlreadyVerified::class);

        $user->inviteSent($messageId);
    }

    public function testInviteSentDeleted(): void
    {
        $faker = $this->faker();
        $messageId = EmailGatewayMessageId::fromString($faker->uuid());

        $user = $this->getUserActive();
        $user->delete();

        $this->expectException(Exception\UserIsDeleted::class);
        $this->expectExceptionMessage(
            sprintf('Tried to send invite to deleted User with ID "%s"', $user->userId()),
        );

        $user->inviteSent($messageId);
    }
}
