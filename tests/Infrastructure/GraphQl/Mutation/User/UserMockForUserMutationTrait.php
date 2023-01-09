<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\GraphQl\Mutation\User;

use App\Entity\User;
use App\Model\User\Name;
use App\Model\User\Token;
use App\Security\Security;
use App\Security\TokenValidator;
use App\Tests\EmptyProvider;
use Mockery;

trait UserMockForUserMutationTrait
{
    use EmptyProvider;

    /**
     * @return User|Mockery\MockInterface
     */
    private function getUserMock(): User
    {
        $faker = $this->faker();

        $user = \Mockery::mock(User::class);
        $user->shouldReceive('email')
            ->once()
            ->andReturn($faker->emailVo());
        $user->shouldReceive('firstName')
            ->once()
            ->andReturn(Name::fromString($faker->firstName()));
        $user->shouldReceive('lastName')
            ->once()
            ->andReturn(Name::fromString($faker->lastName()));

        return $user;
    }

    private function createSecurity(bool $isGrantedResult): Security
    {
        $security = \Mockery::mock(Security::class);
        $security->shouldReceive('isGranted')
            ->once()
            ->andReturn($isGrantedResult);

        return $security;
    }

    private function getTokenValidator(User $user): TokenValidator
    {
        $tokenValidator = \Mockery::mock(TokenValidator::class);
        $tokenValidator->shouldReceive('validate')
            ->once()
            ->with(\Mockery::type(Token::class))
            ->andReturn($user);

        return $tokenValidator;
    }
}
