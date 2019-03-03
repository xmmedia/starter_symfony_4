<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Event;

use App\Model\User\Event\ChangedPassword;
use App\Tests\BaseTestCase;
use App\Tests\CanCreateEventFromArray;

class ChangedPasswordTest extends BaseTestCase
{
    use CanCreateEventFromArray;

    public function testOccur(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $password = $faker->password;

        $event = ChangedPassword::now($userId, $password);

        $this->assertEquals($userId, $event->userId());
        $this->assertEquals($password, $event->encodedPassword());
    }

    public function testFromArray(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $password = $faker->password;

        /** @var ChangedPassword $event */
        $event = $this->createEventFromArray(
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
