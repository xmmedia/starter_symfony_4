<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Entity\User;
use App\Entity\UserToken;
use App\Model\User\Exception\InvalidToken;
use App\Model\User\Exception\TokenHasExpired;
use App\Model\User\Token;
use App\Repository\UserTokenRepository;
use App\Security\TokenValidator;
use App\Tests\BaseTestCase;
use Mockery;

class TokenValidatorTest extends BaseTestCase
{
    public function testValid(): void
    {
        $token = Token::fromString('string');

        $user = Mockery::mock(User::class);
        $user->shouldReceive('active')
            ->once()
            ->andReturnTrue();

        $userToken = Mockery::mock(UserToken::class);
        $userToken->shouldReceive('user')
            ->once()
            ->andReturn($user);
        $userToken->shouldReceive('generatedAt')
            ->once()
            ->andReturn(new \DateTimeImmutable('-5 hours'));

        $tokenRepo = Mockery::mock(UserTokenRepository::class);
        $tokenRepo->shouldReceive('find')
            ->once()
            ->with($token->toString())
            ->andReturn($userToken);

        $result = (new TokenValidator($tokenRepo))->validate($token);

        $this->assertInstanceOf(User::class, $result);
    }

    public function testTokenDoesntExist(): void
    {
        $token = Token::fromString('string');

        $tokenRepo = Mockery::mock(UserTokenRepository::class);
        $tokenRepo->shouldReceive('find')
            ->with($token->toString())
            ->andReturnNull();

        $this->expectException(InvalidToken::class);

        (new TokenValidator($tokenRepo))->validate($token);
    }

    public function testUserInactive(): void
    {
        $token = Token::fromString('string');

        $user = Mockery::mock(User::class);
        $user->shouldReceive('active')
            ->andReturnFalse();

        $userToken = Mockery::mock(UserToken::class);
        $userToken->shouldReceive('user')
            ->andReturn($user);

        $tokenRepo = Mockery::mock(UserTokenRepository::class);
        $tokenRepo->shouldReceive('find')
            ->with($token->toString())
            ->andReturn($userToken);

        $this->expectException(InvalidToken::class);

        (new TokenValidator($tokenRepo))->validate($token);
    }

    public function testExpiredToken(): void
    {
        $token = Token::fromString('string');

        $user = Mockery::mock(User::class);
        $user->shouldReceive('active')
            ->andReturnTrue();

        $userToken = Mockery::mock(UserToken::class);
        $userToken->shouldReceive('user')
            ->andReturn($user);
        $userToken->shouldReceive('generatedAt')
            ->andReturn(new \DateTimeImmutable('-48 hours'));

        $tokenRepo = Mockery::mock(UserTokenRepository::class);
        $tokenRepo->shouldReceive('find')
            ->with($token->toString())
            ->andReturn($userToken);

        $this->expectException(TokenHasExpired::class);

        (new TokenValidator($tokenRepo))->validate($token);
    }
}
