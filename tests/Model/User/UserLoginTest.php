<?php

declare(strict_types=1);

namespace App\Tests\Model\User;

use App\Model\EmailGatewayMessageId;
use App\Model\User\Name;
use App\Model\User\Role;
use App\Model\User\Token;
use App\Model\User\User;
use App\Model\User\Event;
use App\Model\User\Exception;
use App\Tests\BaseTestCase;
use App\Tests\FakeAr;

class UserLoginTest extends BaseTestCase
{
    use UserTestTrait;

    public function testLoggedIn(): void
    {
        $user = $this->getUserActive();

        $user->loggedIn();

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\UserLoggedIn::class,
            [],
            $events
        );

        $this->assertCount(1, $events);
    }

    public function testLoggedInUnverified(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $user = User::createByAdmin(
            $userId,
            $email,
            $password,
            $role,
            true,
            $firstName,
            $lastName,
            true,
            $this->userUniquenessCheckerNone
        );

        $this->expectException(Exception\UserNotVerified::class);

        $user->loggedIn();
    }

    public function testLoggedInInactive(): void
    {
        $user = $this->getUserInactive();

        $this->expectException(Exception\InvalidUserActiveStatus::class);

        $user->loggedIn();
    }
}
