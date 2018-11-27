<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Event;

use App\Model\Email;
use App\Model\User\Event\UserUpdatedProfile;
use App\Model\User\Name;
use App\Model\User\UserId;
use App\Tests\CanCreateEvent;
use Faker;
use PHPUnit\Framework\TestCase;

class UserUpdatedProfileTest extends TestCase
{
    use CanCreateEvent;

    public function testOccur(): void
    {
        $faker = Faker\Factory::create();

        $userId = UserId::generate();
        $email = Email::fromString($faker->email);
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $event = UserUpdatedProfile::now($userId, $email, $firstName, $lastName);

        $this->assertEquals($userId, $event->userId());
        $this->assertEquals($email, $event->email());
        $this->assertEquals($firstName, $event->firstName());
        $this->assertEquals($lastName, $event->lastName());
    }

    public function testFromArray(): void
    {
        $faker = Faker\Factory::create();

        $userId = UserId::generate();
        $email = Email::fromString($faker->email);
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        /** @var UserUpdatedProfile $event */
        $event = $this->createEvent(
            UserUpdatedProfile::class,
            $userId->toString(),
            [
                'email'     => $email->toString(),
                'firstName' => $firstName->toString(),
                'lastName'  => $lastName->toString(),
            ]
        );

        $this->assertInstanceOf(UserUpdatedProfile::class, $event);

        $this->assertEquals($userId, $event->userId());
        $this->assertEquals($email, $event->email());
        $this->assertEquals($firstName, $event->firstName());
        $this->assertEquals($lastName, $event->lastName());
    }
}
