<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Security\PasswordEncoder;
use Faker;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Role\Role;

class PasswordEncoderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @dataProvider roleProvider
     */
    public function test(string $role): void
    {
        $faker = Faker\Factory::create();

        $passwordEncoder = Mockery::mock(UserPasswordEncoderInterface::class);
        $passwordEncoder->shouldReceive('encodePassword')
            ->withArgs(function ($user, $password) use ($role) {
                $this->assertEquals($role, $user->roles()[0]);

                return true;
            });

        (new PasswordEncoder($passwordEncoder))(
            new Role($role),
            $faker->password
        );
    }

    public function roleProvider(): \Generator
    {
        yield ['ROLE_USER'];
        yield ['ROLE_ADMIN'];
        yield ['ROLE_SUPER_ADMIN'];
    }
}
