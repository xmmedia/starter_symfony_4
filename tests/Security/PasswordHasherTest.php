<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Model\User\Role;
use App\Security\PasswordHasher;
use App\Tests\BaseTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordHasherTest extends BaseTestCase
{
    /**
     * @dataProvider roleProvider
     */
    public function test(Role $role): void
    {
        $faker = $this->faker();

        $passwordHasher = \Mockery::mock(UserPasswordHasherInterface::class);
        $passwordHasher->shouldReceive('hashPassword')
            ->withArgs(function ($user, $password) use ($role): bool {
                $this->assertEquals($role, $user->firstRole());

                return true;
            })
            ->andReturn('hashed-password');

        (new PasswordHasher($passwordHasher))($role, $faker->password());
    }

    public static function roleProvider(): \Generator
    {
        foreach (Role::getEnumerators() as $role) {
            yield [$role];
        }
    }
}
