<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\GraphQl\Resolver\User;

use App\Entity\User;
use App\Infrastructure\GraphQl\Resolver\User\UserPasswordValidResolver;
use App\Security\Security;
use App\Tests\BaseTestCase;
use Mockery;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserPasswordValidResolverTest extends BaseTestCase
{
    public function testPasswordMatches(): void
    {
        $faker = $this->faker();
        $password = $faker->password();

        $currentUser = Mockery::mock(User::class);

        $userPasswordEncoder = Mockery::mock(UserPasswordEncoderInterface::class);
        $userPasswordEncoder->shouldReceive('isPasswordValid')
            ->once()
            ->with($currentUser, $password)
            ->andReturnTrue();

        $security = Mockery::mock(Security::class);
        $security->shouldReceive('getUser')
            ->once()
            ->andReturn($currentUser);

        $result = (new UserPasswordValidResolver(
            $userPasswordEncoder,
            $security
        ))(
            $password
        );

        $this->assertEquals(['valid' => true], $result);
    }

    public function testPasswordDifferent(): void
    {
        $faker = $this->faker();
        $password = $faker->password();

        $currentUser = Mockery::mock(User::class);

        $userPasswordEncoder = Mockery::mock(UserPasswordEncoderInterface::class);
        $userPasswordEncoder->shouldReceive('isPasswordValid')
            ->once()
            ->with($currentUser, $password)
            ->andReturnFalse();

        $security = Mockery::mock(Security::class);
        $security->shouldReceive('getUser')
            ->once()
            ->andReturn($currentUser);

        $result = (new UserPasswordValidResolver(
            $userPasswordEncoder,
            $security
        ))(
            $password
        );

        $this->assertEquals(['valid' => false], $result);
    }

    public function testNotLoggedIn(): void
    {
        $faker = $this->faker();

        $userPasswordEncoder = Mockery::mock(UserPasswordEncoderInterface::class);

        $security = Mockery::mock(Security::class);
        $security->shouldReceive('getUser')
            ->once()
            ->andReturnNull();

        $this->expectException(\RuntimeException::class);

        (new UserPasswordValidResolver(
            $userPasswordEncoder,
            $security
        ))(
            $faker->password()
        );
    }
}
