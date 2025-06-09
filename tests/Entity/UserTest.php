<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\User;
use App\Model\User\Role;
use App\Tests\BaseTestCase;
use Ramsey\Uuid\Uuid;

class UserTest extends BaseTestCase
{
    public function testUserId(): void
    {
        $faker = $this->faker();

        $userId = Uuid::fromString($faker->uuid());

        $user = new User();
        $reflection = new \ReflectionClass(User::class);
        $reflection->getProperty('userId')
            ->setValue($user, $userId);

        $this->assertEquals($userId->toString(), $user->userId()->toString());
    }

    public function testUsername(): void
    {
        $faker = $this->faker();

        $email = $faker->email();

        $user = new User();
        $reflection = new \ReflectionClass(User::class);
        $reflection->getProperty('email')
            ->setValue($user, $email);

        $this->assertEquals($email, $user->email()->toString());
        $this->assertEquals($email, $user->getUserIdentifier());
    }

    public function testPassword(): void
    {
        $faker = $this->faker();

        $password = $faker->password();

        $user = new User();

        $reflection = new \ReflectionClass(User::class);
        $reflection->getProperty('password')
            ->setValue($user, $password);

        $this->assertEquals($password, $user->password());
        $this->assertEquals($password, $user->getPassword());
    }

    public function testEraseCredentials(): void
    {
        $faker = $this->faker();

        $user = new User();

        $reflection = new \ReflectionClass(User::class);
        $reflection->getProperty('password')
            ->setValue($user, $faker->password());

        // this shouldn't do anything so just make sure the password still returns value
        $user->eraseCredentials();

        $this->assertNotNull($user->password());
    }

    public function testFlags(): void
    {
        $user = new User();

        $this->assertFalse($user->verified());
        $this->assertFalse($user->active());

        $reflection = new \ReflectionClass(User::class);
        $reflection->getProperty('verified')
            ->setValue($user, true);
        $reflection->getProperty('active')
            ->setValue($user, true);

        $this->assertTrue($user->verified());
        $this->assertTrue($user->active());
    }

    public function testNoSalt(): void
    {
        $user = new User();

        $this->assertNull($user->getSalt());
    }

    public function testName(): void
    {
        $faker = $this->faker();

        $firstName = $faker->name();
        $lastName = $faker->name();

        $user = new User();
        $reflection = new \ReflectionClass(User::class);
        $reflection->getProperty('firstName')
            ->setValue($user, $firstName);
        $reflection->getProperty('lastName')
            // note the added space
            ->setValue($user, $lastName.' ');

        $this->assertEquals($firstName.' '.$lastName, $user->name());
        $this->assertEquals($firstName, $user->firstName()->toString());
        $this->assertEquals($lastName, $user->lastName()->toString());
    }

    public function testFirstNameNull(): void
    {
        $user = new User();

        $this->assertNull($user->firstName());
    }

    public function testLastNameNull(): void
    {
        $user = new User();

        $this->assertNull($user->lastName());
    }

    public function testLastLogin(): void
    {
        $faker = $this->faker();

        $lastLogin = \DateTimeImmutable::createFromMutable($faker->dateTime());

        $user = new User();
        $reflection = new \ReflectionClass(User::class);
        $reflection->getProperty('lastLogin')
            ->setValue($user, $lastLogin);

        $this->assertEquals($lastLogin, $user->lastLogin());
    }

    public function testLoginCount(): void
    {
        $faker = $this->faker();

        $loginCount = $faker->randomNumber();

        $user = new User();

        $this->assertEquals(0, $user->loginCount());

        $reflection = new \ReflectionClass(User::class);
        $reflection->getProperty('loginCount')
            ->setValue($user, $loginCount);

        $this->assertEquals($loginCount, $user->loginCount());
    }

    public function testLastLoginNull(): void
    {
        $user = new User();

        $this->assertNull($user->lastLogin());
    }

    public function testRolesNone(): void
    {
        $user = new User();

        $this->assertEquals(['ROLE_USER'], $user->roles());
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
    }

    public function testRolesDuplicateRoleUser(): void
    {
        $user = new User();
        $reflection = new \ReflectionClass(User::class);
        $reflection->getProperty('roles')
            ->setValue($user, [
                Role::ROLE_USER()->getValue(),
            ]);

        $this->assertEquals(['ROLE_USER'], $user->roles());
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
    }

    public function testRolesDuplicateRole(): void
    {
        $user = new User();
        $reflection = new \ReflectionClass(User::class);
        $reflection->getProperty('roles')
            ->setValue($user, [
                Role::ROLE_USER()->getValue(),
                Role::ROLE_USER()->getValue(),
            ]);

        $this->assertEquals(['ROLE_USER'], $user->roles());
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
    }

    public function testRolesDuplicateRoleAdmin(): void
    {
        $user = new User();
        $reflection = new \ReflectionClass(User::class);
        $reflection->getProperty('roles')
            ->setValue($user, [
                Role::ROLE_ADMIN()->getValue(),
            ]);

        $this->assertEquals(['ROLE_ADMIN', 'ROLE_USER'], $user->roles());
        $this->assertEquals(['ROLE_ADMIN', 'ROLE_USER'], $user->getRoles());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('userEqualProvider')]
    public function testEqualTo(User $user1, User $user2, bool $result): void
    {
        $this->assertEquals($result, $user1->isEqualTo($user2));
    }

    public static function userEqualProvider(): \Generator
    {
        $faker = self::makeFaker();

        $user1 = new User();
        $reflection = new \ReflectionClass(User::class);
        $reflection->getProperty('password')
            ->setValue($user1, $faker->password());

        $user2 = new User();
        $reflection = new \ReflectionClass(User::class);
        $reflection->getProperty('password')
            ->setValue($user2, $faker->password());

        // password has changed
        yield [$user1, $user2, false];

        $password = $faker->password();

        $user1 = new User();
        $reflection = new \ReflectionClass(User::class);
        $reflection->getProperty('password')
            ->setValue($user1, $password);
        $reflection->getProperty('email')
            ->setValue($user1, $faker->email());

        $user2 = new User();
        $reflection = new \ReflectionClass(User::class);
        $reflection->getProperty('password')
            ->setValue($user2, $password);
        $reflection->getProperty('email')
            ->setValue($user2, $faker->email());

        // email (username) has changed
        yield [$user1, $user2, false];

        $password = $faker->password();
        $email = $faker->email();

        $user1 = new User();
        $reflection = new \ReflectionClass(User::class);
        $reflection->getProperty('password')
            ->setValue($user1, $password);
        $reflection->getProperty('email')
            ->setValue($user1, $email);

        $user2 = new User();
        $reflection = new \ReflectionClass(User::class);
        $reflection->getProperty('password')
            ->setValue($user2, $password);
        $reflection->getProperty('email')
            ->setValue($user2, $email);
        $reflection->getProperty('active')
            ->setValue($user2, false);

        // no longer active
        yield [$user1, $user2, false];

        $password = $faker->password();
        $email = $faker->email();

        $user1 = new User();
        $reflection = new \ReflectionClass(User::class);
        $reflection->getProperty('password')
            ->setValue($user1, $password);
        $reflection->getProperty('email')
            ->setValue($user1, $email);

        $user2 = new User();
        $reflection = new \ReflectionClass(User::class);
        $reflection->getProperty('password')
            ->setValue($user2, $password);
        $reflection->getProperty('email')
            ->setValue($user2, $email);
        $reflection->getProperty('active')
            ->setValue($user2, true);
        $reflection->getProperty('verified')
            ->setValue($user2, false);

        // no longer verified
        yield [$user1, $user2, false];

        $password = $faker->password();
        $email = $faker->email();

        $user1 = new User();
        $reflection = new \ReflectionClass(User::class);
        $reflection->getProperty('password')
            ->setValue($user1, $password);
        $reflection->getProperty('email')
            ->setValue($user1, $email);
        $reflection->getProperty('roles')
            ->setValue($user1, ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN']);

        $user2 = new User();
        $reflection = new \ReflectionClass(User::class);
        $reflection->getProperty('password')
            ->setValue($user2, $password);
        $reflection->getProperty('email')
            ->setValue($user2, $email);
        $reflection->getProperty('active')
            ->setValue($user2, true);
        $reflection->getProperty('verified')
            ->setValue($user2, true);
        $reflection->getProperty('roles')
            ->setValue($user2, ['ROLE_ADMIN']);

        // roles have changed (no longer has super admin)
        yield [$user1, $user2, false];

        $password = $faker->password();
        $email = $faker->email();

        $user1 = new User();
        $reflection = new \ReflectionClass(User::class);
        $reflection->getProperty('password')
            ->setValue($user1, $password);
        $reflection->getProperty('email')
            ->setValue($user1, $email);
        $reflection->getProperty('roles')
            ->setValue($user1, ['ROLE_ADMIN']);

        $user2 = new User();
        $reflection = new \ReflectionClass(User::class);
        $reflection->getProperty('password')
            ->setValue($user2, $password);
        $reflection->getProperty('email')
            ->setValue($user2, $email);
        $reflection->getProperty('active')
            ->setValue($user2, true);
        $reflection->getProperty('verified')
            ->setValue($user2, true);
        $reflection->getProperty('roles')
            ->setValue($user2, ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN']);

        // roles have changed (gained super admin)
        yield [$user1, $user2, false];

        $password = $faker->password();
        $email = $faker->email();

        $user1 = new User();
        $reflection = new \ReflectionClass(User::class);
        $reflection->getProperty('password')
            ->setValue($user1, $password);
        $reflection->getProperty('email')
            ->setValue($user1, $email);
        $reflection->getProperty('roles')
            ->setValue($user1, ['ROLE_USER']);

        $user2 = new User();
        $reflection = new \ReflectionClass(User::class);
        $reflection->getProperty('password')
            ->setValue($user2, $password);
        $reflection->getProperty('email')
            ->setValue($user2, $email);
        $reflection->getProperty('active')
            ->setValue($user2, true);
        $reflection->getProperty('verified')
            ->setValue($user2, true);
        $reflection->getProperty('roles')
            ->setValue($user2, ['ROLE_ADMIN']);

        // roles have changed (switched from user to admin)
        yield [$user1, $user2, false];

        $password = $faker->password();
        $email = $faker->email();

        $user1 = new User();
        $reflection = new \ReflectionClass(User::class);
        $reflection->getProperty('password')
            ->setValue($user1, $password);
        $reflection->getProperty('email')
            ->setValue($user1, $email);

        $user2 = new User();
        $reflection = new \ReflectionClass(User::class);
        $reflection->getProperty('password')
            ->setValue($user2, $password);
        $reflection->getProperty('email')
            ->setValue($user2, $email);
        $reflection->getProperty('active')
            ->setValue($user2, true);
        $reflection->getProperty('verified')
            ->setValue($user2, true);

        // equal
        yield [$user1, $user2, true];
    }
}
