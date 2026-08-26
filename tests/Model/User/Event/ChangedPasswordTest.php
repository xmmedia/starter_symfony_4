<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Event;

use App\Model\User\Event\ChangedPassword;
use App\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\CanCreateEventFromArray;

class ChangedPasswordTest extends BaseTestCase
{
    use CanCreateEventFromArray;

    public function testOccur(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();

        $event = ChangedPassword::now($userId);

        $this->assertEquals($userId, $event->userId());
    }

    public function testFromArray(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();

        $event = $this->createEventFromArray(
            ChangedPassword::class,
            $userId->toString(),
        );

        $this->assertInstanceOf(ChangedPassword::class, $event);

        $this->assertEquals($userId, $event->userId());
    }
}
