<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Event;

use App\Model\User\Event\AdminChangedPassword;
use App\Model\User\UserId;
use App\Tests\CanCreateEvent;
use Faker;
use PHPUnit\Framework\TestCase;

class AdminChangedPasswordTest extends TestCase
{
    use CanCreateEvent;

    public function testOccur(): void
    {
        $faker = Faker\Factory::create();

        $userId = UserId::generate();
        $password = $faker->password;

        $event = AdminChangedPassword::now($userId, $password);

        $this->assertEquals($userId, $event->userId());
        $this->assertEquals($password, $event->encodedPassword());
    }

    public function testFromArray(): void
    {
        $faker = Faker\Factory::create();

        $userId = UserId::generate();
        $password = $faker->password;

        /** @var AdminChangedPassword $event */
        $event = $this->createEvent(
            AdminChangedPassword::class,
            $userId->toString(),
            [
                'encodedPassword' => $password,
            ]
        );

        $this->assertInstanceOf(AdminChangedPassword::class, $event);

        $this->assertEquals($userId, $event->userId());
        $this->assertEquals($password, $event->encodedPassword());
    }
}
