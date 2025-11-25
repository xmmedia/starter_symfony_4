<?php

declare(strict_types=1);

namespace App\Tests\Projection\User;

use App\Entity\User;
use App\Entity\UserToken;
use App\Projection\User\UserTokenFinder;
use App\Tests\BaseTestCase;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;

class UserTokenFinderTest extends BaseTestCase
{
    public function testCreateResetPasswordRequest(): void
    {
        $expiresAt = new \DateTimeImmutable('+1 hour');
        $selector = bin2hex(random_bytes(20));
        $hashedToken = bin2hex(random_bytes(20));

        $user = \Mockery::mock(User::class);

        $registry = \Mockery::mock(ManagerRegistry::class);
        $registry->shouldReceive('getManagerForClass')
            ->with(UserToken::class)
            ->andReturn(\Mockery::mock(ObjectManager::class));

        $finder = new UserTokenFinder($registry);

        $result = $finder->createResetPasswordRequest($user, $expiresAt, $selector, $hashedToken);

        $this->assertSame($user, $result->getUser());
        $this->assertNotNull($result->getId());
    }

    public function testCreateResetPasswordRequestWithExactValues(): void
    {
        $expiresAt = new \DateTimeImmutable('+1 hour');
        $selector = bin2hex(random_bytes(20));
        $hashedToken = bin2hex(random_bytes(20));

        $user = \Mockery::mock(User::class);

        $registry = \Mockery::mock(ManagerRegistry::class);
        $registry->shouldReceive('getManagerForClass')
            ->with(UserToken::class)
            ->andReturn(\Mockery::mock(ObjectManager::class));

        $finder = new UserTokenFinder($registry);

        $token = $finder->createResetPasswordRequest($user, $expiresAt, $selector, $hashedToken);

        $this->assertInstanceOf(UserToken::class, $token);
        $this->assertSame($user, $token->user());

        // Verify the token is properly configured
        $reflection = new \ReflectionClass($token);
        $selectorProperty = $reflection->getProperty('selector');
        $this->assertEquals($selector, $selectorProperty->getValue($token));

        $hashedTokenProperty = $reflection->getProperty('hashedToken');
        $this->assertEquals($hashedToken, $hashedTokenProperty->getValue($token));

        $expiresAtProperty = $reflection->getProperty('expiresAt');
        $this->assertEquals($expiresAt, $expiresAtProperty->getValue($token));
    }

    public function testCreateMultipleResetPasswordRequests(): void
    {
        $expiresAt1 = new \DateTimeImmutable('+1 hour');
        $expiresAt2 = new \DateTimeImmutable('+2 hours');
        $selector1 = 'selector-1';
        $selector2 = 'selector-2';
        $hashedToken1 = 'token-1';
        $hashedToken2 = 'token-2';

        $user1 = \Mockery::mock(User::class);
        $user2 = \Mockery::mock(User::class);
        
        $registry = \Mockery::mock(ManagerRegistry::class);
        $registry->shouldReceive('getManagerForClass')
            ->with(UserToken::class)
            ->andReturn(\Mockery::mock(ObjectManager::class));

        $finder = new UserTokenFinder($registry);

        $token1 = $finder->createResetPasswordRequest($user1, $expiresAt1, $selector1, $hashedToken1);
        $token2 = $finder->createResetPasswordRequest($user2, $expiresAt2, $selector2, $hashedToken2);

        $this->assertNotEquals($token1->getId(), $token2->getId());
        $this->assertSame($user1, $token1->user());
        $this->assertSame($user2, $token2->user());
    }

    public function testInheritsFromServiceEntityRepository(): void
    {
        $objectManager = \Mockery::mock(ObjectManager::class);
        $objectManager->shouldReceive('getClassMetadata')
            ->with(UserToken::class)
            ->andReturn(\Mockery::mock(\Doctrine\ORM\Mapping\ClassMetadata::class));

        $registry = \Mockery::mock(ManagerRegistry::class);
        $registry->shouldReceive('getManagerForClass')
            ->with(UserToken::class)
            ->andReturn($objectManager);

        $finder = new UserTokenFinder($registry);

        // Verify it implements the correct interface
        $this->assertInstanceOf(\SymfonyCasts\Bundle\ResetPassword\Persistence\ResetPasswordRequestRepositoryInterface::class, $finder);
    }
}
