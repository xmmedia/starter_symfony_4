<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\User;
use App\Tests\BaseTestCase;

class UserTest extends BaseTestCase
{
    public function testName(): void
    {
        $faker = $this->faker();

        $firstName = $faker->name;
        $lastName = $faker->name;

        $user = new User();
        $reflection = new \ReflectionClass(User::class);
        $property = $reflection->getProperty('firstName');
        $property->setAccessible(true);
        $property->setValue($user, $firstName);
        $property = $reflection->getProperty('lastName');
        $property->setAccessible(true);
        // note the added space
        $property->setValue($user, $lastName.' ');

        $name = $firstName.' '.$lastName;

        $this->assertEquals($name, $user->name());
    }

    /**
     * @dataProvider roleProvider
     */
    public function testEncoder(string $role, $expected): void
    {
        $user = new User();
        $reflection = new \ReflectionClass(User::class);
        $property = $reflection->getProperty('roles');
        $property->setAccessible(true);
        $property->setValue($user, [$role]);

        $this->assertEquals($expected, $user->getEncoderName());
    }

    public function roleProvider(): \Generator
    {
        yield ['ROLE_USER', null];
        yield ['ROLE_ADMIN', 'harsh'];
        yield ['ROLE_SUPER_ADMIN', 'harsh'];
    }

    /**
     * @dataProvider userEqualProvider
     */
    public function testEqualTo(User $user1, User $user2, bool $result): void
    {
        $this->assertEquals($result, $user1->isEqualTo($user2));
    }

    public function userEqualProvider(): \Generator
    {
        $faker = $this->faker();

        $user1 = new User();
        $reflection = new \ReflectionClass(User::class);
        $property = $reflection->getProperty('password');
        $property->setAccessible(true);
        $property->setValue($user1, $faker->password);

        $user2 = new User();
        $reflection = new \ReflectionClass(User::class);
        $property = $reflection->getProperty('password');
        $property->setAccessible(true);
        $property->setValue($user2, $faker->password);

        // password has changed
        yield [$user1, $user2, false];

        $password = $faker->password;

        $user1 = new User();
        $reflection = new \ReflectionClass(User::class);
        $property = $reflection->getProperty('password');
        $property->setAccessible(true);
        $property->setValue($user1, $password);
        $property = $reflection->getProperty('email');
        $property->setAccessible(true);
        $property->setValue($user1, $faker->email);

        $user2 = new User();
        $reflection = new \ReflectionClass(User::class);
        $property = $reflection->getProperty('password');
        $property->setAccessible(true);
        $property->setValue($user2, $password);
        $property = $reflection->getProperty('email');
        $property->setAccessible(true);
        $property->setValue($user2, $faker->email);

        // email (username) has changed
        yield [$user1, $user2, false];

        $password = $faker->password;
        $email = $faker->email;

        $user1 = new User();
        $reflection = new \ReflectionClass(User::class);
        $property = $reflection->getProperty('password');
        $property->setAccessible(true);
        $property->setValue($user1, $password);
        $property = $reflection->getProperty('email');
        $property->setAccessible(true);
        $property->setValue($user1, $email);

        $user2 = new User();
        $reflection = new \ReflectionClass(User::class);
        $property = $reflection->getProperty('password');
        $property->setAccessible(true);
        $property->setValue($user2, $password);
        $property = $reflection->getProperty('email');
        $property->setAccessible(true);
        $property->setValue($user2, $email);
        $property = $reflection->getProperty('active');
        $property->setAccessible(true);
        $property->setValue($user2, false);

        // no longer active
        yield [$user1, $user2, false];

        $password = $faker->password;
        $email = $faker->email;

        $user1 = new User();
        $reflection = new \ReflectionClass(User::class);
        $property = $reflection->getProperty('password');
        $property->setAccessible(true);
        $property->setValue($user1, $password);
        $property = $reflection->getProperty('email');
        $property->setAccessible(true);
        $property->setValue($user1, $email);

        $user2 = new User();
        $reflection = new \ReflectionClass(User::class);
        $property = $reflection->getProperty('password');
        $property->setAccessible(true);
        $property->setValue($user2, $password);
        $property = $reflection->getProperty('email');
        $property->setAccessible(true);
        $property->setValue($user2, $email);
        $property = $reflection->getProperty('active');
        $property->setAccessible(true);
        $property->setValue($user2, true);
        $property = $reflection->getProperty('verified');
        $property->setAccessible(true);
        $property->setValue($user2, false);

        // no longer verified
        yield [$user1, $user2, false];

        $password = $faker->password;
        $email = $faker->email;

        $user1 = new User();
        $reflection = new \ReflectionClass(User::class);
        $property = $reflection->getProperty('password');
        $property->setAccessible(true);
        $property->setValue($user1, $password);
        $property = $reflection->getProperty('email');
        $property->setAccessible(true);
        $property->setValue($user1, $email);
        $property = $reflection->getProperty('roles');
        $property->setAccessible(true);
        $property->setValue($user1, ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN']);

        $user2 = new User();
        $reflection = new \ReflectionClass(User::class);
        $property = $reflection->getProperty('password');
        $property->setAccessible(true);
        $property->setValue($user2, $password);
        $property = $reflection->getProperty('email');
        $property->setAccessible(true);
        $property->setValue($user2, $email);
        $property = $reflection->getProperty('active');
        $property->setAccessible(true);
        $property->setValue($user2, true);
        $property = $reflection->getProperty('verified');
        $property->setAccessible(true);
        $property->setValue($user2, true);
        $property = $reflection->getProperty('roles');
        $property->setAccessible(true);
        $property->setValue($user2, ['ROLE_ADMIN']);

        // roles have changed (no longer has super admin)
        yield [$user1, $user2, false];

        $password = $faker->password;
        $email = $faker->email;

        $user1 = new User();
        $reflection = new \ReflectionClass(User::class);
        $property = $reflection->getProperty('password');
        $property->setAccessible(true);
        $property->setValue($user1, $password);
        $property = $reflection->getProperty('email');
        $property->setAccessible(true);
        $property->setValue($user1, $email);
        $property = $reflection->getProperty('roles');
        $property->setAccessible(true);
        $property->setValue($user1, ['ROLE_ADMIN']);

        $user2 = new User();
        $reflection = new \ReflectionClass(User::class);
        $property = $reflection->getProperty('password');
        $property->setAccessible(true);
        $property->setValue($user2, $password);
        $property = $reflection->getProperty('email');
        $property->setAccessible(true);
        $property->setValue($user2, $email);
        $property = $reflection->getProperty('active');
        $property->setAccessible(true);
        $property->setValue($user2, true);
        $property = $reflection->getProperty('verified');
        $property->setAccessible(true);
        $property->setValue($user2, true);
        $property = $reflection->getProperty('roles');
        $property->setAccessible(true);
        $property->setValue($user2, ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN']);

        // roles have changed (gained super admin)
        yield [$user1, $user2, false];

        $password = $faker->password;
        $email = $faker->email;

        $user1 = new User();
        $reflection = new \ReflectionClass(User::class);
        $property = $reflection->getProperty('password');
        $property->setAccessible(true);
        $property->setValue($user1, $password);
        $property = $reflection->getProperty('email');
        $property->setAccessible(true);
        $property->setValue($user1, $email);
        $property = $reflection->getProperty('roles');
        $property->setAccessible(true);
        $property->setValue($user1, ['ROLE_USER']);

        $user2 = new User();
        $reflection = new \ReflectionClass(User::class);
        $property = $reflection->getProperty('password');
        $property->setAccessible(true);
        $property->setValue($user2, $password);
        $property = $reflection->getProperty('email');
        $property->setAccessible(true);
        $property->setValue($user2, $email);
        $property = $reflection->getProperty('active');
        $property->setAccessible(true);
        $property->setValue($user2, true);
        $property = $reflection->getProperty('verified');
        $property->setAccessible(true);
        $property->setValue($user2, true);
        $property = $reflection->getProperty('roles');
        $property->setAccessible(true);
        $property->setValue($user2, ['ROLE_ADMIN']);

        // roles have changed (switched from user to admin)
        yield [$user1, $user2, false];

        $password = $faker->password;
        $email = $faker->email;

        $user1 = new User();
        $reflection = new \ReflectionClass(User::class);
        $property = $reflection->getProperty('password');
        $property->setAccessible(true);
        $property->setValue($user1, $password);
        $property = $reflection->getProperty('email');
        $property->setAccessible(true);
        $property->setValue($user1, $email);

        $user2 = new User();
        $reflection = new \ReflectionClass(User::class);
        $property = $reflection->getProperty('password');
        $property->setAccessible(true);
        $property->setValue($user2, $password);
        $property = $reflection->getProperty('email');
        $property->setAccessible(true);
        $property->setValue($user2, $email);
        $property = $reflection->getProperty('active');
        $property->setAccessible(true);
        $property->setValue($user2, true);
        $property = $reflection->getProperty('verified');
        $property->setAccessible(true);
        $property->setValue($user2, true);

        // equal
        yield [$user1, $user2, true];
    }
}
