<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Event;

use App\Model\User\Event\ChangedPassword;
use App\Model\User\UserId;
use App\Tests\CanCreateEvent;
use Faker;
use PHPUnit\Framework\TestCase;

class ChangedPasswordTest extends TestCase
{
    use CanCreateEvent;

    public function testOccur(): void
    {
        $faker = Faker\Factory::create();

        $userId = UserId::generate();
        $password = $faker->password;

        $event = ChangedPassword::now($userId, $password);

        $this->assertEquals($userId, $event->userId());
        $this->assertEquals($password, $event->encodedPassword());
    }

    public function testFromArray(): void
    {
        $faker = Faker\Factory::create();

        $userId = UserId::generate();
        $password = $faker->password;

        /** @var ChangedPassword $event */
        $event = $this->createEvent(
            ChangedPassword::class,
            $userId->toString(),
            [
                'encodedPassword' => $password,
            ]
        );

        $this->assertInstanceOf(ChangedPassword::class, $event);

        $this->assertEquals($userId, $event->userId());
        $this->assertEquals($password, $event->encodedPassword());
    }
}
