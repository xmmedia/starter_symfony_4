<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Model\User\Role;
use App\Security\PasswordEncoder;
use App\Tests\BaseTestCase;
use Mockery;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PasswordEncoderTest extends BaseTestCase
{
    /**
     * @dataProvider roleProvider
     */
    public function test(Role $role): void
    {
        $faker = $this->faker();

        $passwordEncoder = Mockery::mock(UserPasswordEncoderInterface::class);
        $passwordEncoder->shouldReceive('encodePassword')
            ->withArgs(function ($user, $password) use ($role): bool {
                $this->assertEquals($role, $user->firstRole());

                return true;
            })
            ->andReturn('encoded-password');

        (new PasswordEncoder($passwordEncoder))($role, $faker->password);
    }

    public function roleProvider(): \Generator
    {
        foreach (Role::getEnumerators() as $role) {
            yield [$role];
        }
    }
}
