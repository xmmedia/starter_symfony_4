<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\User;
use Faker;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testName(): void
    {
        $faker = Faker\Factory::create();

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
        $faker = Faker\Factory::create();

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

        yield [$user1, $user2, true];
    }
}
