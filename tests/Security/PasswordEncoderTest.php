<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Security\PasswordEncoder;
use App\Tests\BaseTestCase;
use Mockery;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Role\Role;

class PasswordEncoderTest extends BaseTestCase
{
    /**
     * @dataProvider roleProvider
     */
    public function test(string $role): void
    {
        $faker = $this->faker();

        $passwordEncoder = Mockery::mock(UserPasswordEncoderInterface::class);
        $passwordEncoder->shouldReceive('encodePassword')
            ->withArgs(function ($user, $password) use ($role): bool {
                $this->assertEquals($role, $user->roles()[0]);

                return true;
            });

        (new PasswordEncoder($passwordEncoder))(
            new Role($role),
            $faker->password(12, 250)
        );
    }

    public function roleProvider(): \Generator
    {
        yield ['ROLE_USER'];
        yield ['ROLE_ADMIN'];
        yield ['ROLE_SUPER_ADMIN'];
    }
}
