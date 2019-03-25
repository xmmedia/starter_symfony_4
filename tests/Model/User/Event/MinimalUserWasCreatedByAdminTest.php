<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Event;

use App\Model\User\Event\MinimalUserWasCreatedByAdmin;
use App\Model\User\Role;
use App\Tests\BaseTestCase;
use App\Tests\CanCreateEventFromArray;

class MinimalUserWasCreatedByAdminTest extends BaseTestCase
{
    use CanCreateEventFromArray;

    public function testOccur(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();

        $event = MinimalUserWasCreatedByAdmin::now($userId, $email, $password, $role);

        $this->assertEquals($userId, $event->userId());
        $this->assertEquals($email, $event->email());
        $this->assertEquals($password, $event->encodedPassword());
        $this->assertEquals($role, $event->role());
    }

    public function testFromArray(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();

        /** @var MinimalUserWasCreatedByAdmin $event */
        $event = $this->createEventFromArray(
            MinimalUserWasCreatedByAdmin::class,
            $userId->toString(),
            [
                'email'           => $email->toString(),
                'encodedPassword' => $password,
                'role'            => $role->getValue(),
            ]
        );

        $this->assertInstanceOf(MinimalUserWasCreatedByAdmin::class, $event);

        $this->assertEquals($userId, $event->userId());
        $this->assertEquals($email, $event->email());
        $this->assertEquals($password, $event->encodedPassword());
        $this->assertEquals($role, $event->role());
    }
}
