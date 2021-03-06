<?php

declare(strict_types=1);

namespace App\Tests\Model\User;

use App\Model\User\Event;
use App\Model\User\Exception;
use App\Model\User\Token;
use App\Tests\BaseTestCase;
use Xm\SymfonyBundle\Model\EmailGatewayMessageId;

class UserPasswordTest extends BaseTestCase
{
    use UserTestTrait;

    public function testChangePasswordByAdmin(): void
    {
        $faker = $this->faker();

        $user = $this->getUserActive();

        $password = $faker->password();

        $user->changePasswordByAdmin($password);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\AdminChangedPassword::class,
            [
                'encodedPassword' => $password,
            ],
            $events
        );

        $this->assertCount(1, $events);
    }

    public function testPasswordRecoverySent(): void
    {
        $faker = $this->faker();

        $user = $this->getUserActive();
        $this->popRecordedEvent($user);

        $token = Token::fromString($faker->asciify(str_repeat('*', 25)));
        $messageId = EmailGatewayMessageId::fromString($faker->uuid());

        $user->passwordRecoverySent($token, $messageId);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\PasswordRecoverySent::class,
            [
                'token'     => $token->toString(),
                'messageId' => $messageId->toString(),
            ],
            $events
        );

        $this->assertCount(1, $events);
    }

    public function testPasswordRecoverySentInactive(): void
    {
        $faker = $this->faker();

        $user = $this->getUserInactive();

        $token = Token::fromString($faker->asciify(str_repeat('*', 25)));
        $messageId = EmailGatewayMessageId::fromString($faker->uuid());

        $this->expectException(Exception\InvalidUserActiveStatus::class);

        $user->passwordRecoverySent($token, $messageId);
    }

    public function testChangePassword(): void
    {
        $faker = $this->faker();

        $password = $faker->password();

        $user = $this->getUserActive();

        $user->changePassword($password);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\ChangedPassword::class,
            ['encodedPassword' => $password],
            $events
        );

        $this->assertCount(1, $events);
    }

    public function testChangePasswordInactive(): void
    {
        $faker = $this->faker();

        $password = $faker->password();

        $user = $this->getUserInactive();

        $this->expectException(Exception\InvalidUserActiveStatus::class);

        $user->changePassword($password);
    }

    public function testUpgradePassword(): void
    {
        $faker = $this->faker();

        $password = $faker->password();

        $user = $this->getUserActive();

        $user->upgradePassword($password);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\PasswordUpgraded::class,
            ['encodedPassword' => $password],
            $events
        );

        $this->assertCount(1, $events);
    }

    public function testUpgradePasswordInactive(): void
    {
        $faker = $this->faker();

        $password = $faker->password();

        $user = $this->getUserInactive();

        $this->expectException(Exception\InvalidUserActiveStatus::class);

        $user->upgradePassword($password);
    }
}
