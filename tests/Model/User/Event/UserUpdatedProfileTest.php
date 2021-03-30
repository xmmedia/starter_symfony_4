<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Event;

use App\Model\User\Event\UserUpdatedProfile;
use App\Model\User\Name;
use App\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\CanCreateEventFromArray;

class UserUpdatedProfileTest extends BaseTestCase
{
    use CanCreateEventFromArray;

    public function testOccur(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();
        $email = $faker->emailVo();
        $firstName = Name::fromString($faker->firstName());
        $lastName = Name::fromString($faker->lastName());

        $event = UserUpdatedProfile::now($userId, $email, $firstName, $lastName);

        $this->assertEquals($userId, $event->userId());
        $this->assertEquals($email, $event->email());
        $this->assertEquals($firstName, $event->firstName());
        $this->assertEquals($lastName, $event->lastName());
    }

    public function testFromArray(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();
        $email = $faker->emailVo();
        $firstName = Name::fromString($faker->firstName());
        $lastName = Name::fromString($faker->lastName());

        /** @var UserUpdatedProfile $event */
        $event = $this->createEventFromArray(
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
