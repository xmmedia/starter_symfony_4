<?php

declare(strict_types=1);

namespace App\Tests\Model\User;

use App\Model\User\Event;
use App\Model\User\Exception;
use App\Tests\BaseTestCase;
use Xm\SymfonyBundle\Model\EmailGatewayMessageId;

class UserVerificationTest extends BaseTestCase
{
    use UserTestTrait;

    public function testVerificationSent(): void
    {
        $faker = $this->faker();

        $user = $this->getUserActive(true);

        $messageId = EmailGatewayMessageId::fromString($faker->uuid());

        $user->verificationSent($messageId);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\VerificationSent::class,
            [
                'messageId' => $messageId->toString(),
            ],
            $events,
        );

        $this->assertCount(1, $events);
    }

    public function testVerificationSentAlreadyVerified(): void
    {
        $faker = $this->faker();
        $messageId = EmailGatewayMessageId::fromString($faker->uuid());

        $user = $this->getUserActive();

        $this->expectException(Exception\UserAlreadyVerified::class);

        $user->verificationSent($messageId);
    }

    public function testVerificationSentDeleted(): void
    {
        $faker = $this->faker();
        $messageId = EmailGatewayMessageId::fromString($faker->uuid());

        $user = $this->getUserActive();
        $user->delete();

        $this->expectException(Exception\UserIsDeleted::class);
        $this->expectExceptionMessage(
            \sprintf('Tried to send invite to deleted User with ID "%s"', $user->userId()),
        );

        $user->verificationSent($messageId);
    }
}
