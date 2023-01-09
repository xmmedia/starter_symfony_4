<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Entity\User;
use App\Security\Security;
use App\Tests\BaseTestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class SecurityTest extends BaseTestCase
{
    public function testGetUser(): void
    {
        $symfonySecurity = \Mockery::mock(
            \Symfony\Component\Security\Core\Security::class,
        );
        $symfonySecurity->shouldReceive('getUser')
            ->once()
            ->withNoArgs()
            ->andReturn(\Mockery::mock(User::class));

        $result = (new Security($symfonySecurity))->getUser();

        $this->assertInstanceOf(User::class, $result);
    }

    public function testGetUserNoUser(): void
    {
        $symfonySecurity = \Mockery::mock(
            \Symfony\Component\Security\Core\Security::class,
        );
        $symfonySecurity->shouldReceive('getUser')
            ->once()
            ->withNoArgs()
            ->andReturnNull();

        $result = (new Security($symfonySecurity))->getUser();

        $this->assertNull($result);
    }

    public function testIsGranted(): void
    {
        $attribute = 'test';
        $subject = \Mockery::mock(User::class);

        $symfonySecurity = \Mockery::mock(
            \Symfony\Component\Security\Core\Security::class,
        );
        $symfonySecurity->shouldReceive('isGranted')
            ->once()
            ->with($attribute, $subject)
            ->andReturnTrue();

        $result = (new Security($symfonySecurity))->isGranted($attribute, $subject);

        $this->assertTrue($result);
    }

    public function testIsGrantedNoSubject(): void
    {
        $attribute = 'test';

        $symfonySecurity = \Mockery::mock(
            \Symfony\Component\Security\Core\Security::class,
        );
        $symfonySecurity->shouldReceive('isGranted')
            ->once()
            ->with($attribute, null)
            ->andReturnTrue();

        $result = (new Security($symfonySecurity))->isGranted($attribute);

        $this->assertTrue($result);
    }

    public function testIsLoggedInNoToken(): void
    {
        $symfonySecurity = \Mockery::mock(
            \Symfony\Component\Security\Core\Security::class,
        );
        $symfonySecurity->shouldReceive('getToken')
            ->once()
            ->andReturnNull();

        $result = (new Security($symfonySecurity))->isLoggedIn();

        $this->assertFalse($result);
    }

    public function testIsLoggedInTrue(): void
    {
        $symfonySecurity = \Mockery::mock(
            \Symfony\Component\Security\Core\Security::class,
        );
        $symfonySecurity->shouldReceive('getToken')
            ->once()
            ->andReturn(\Mockery::mock(TokenInterface::class));
        $symfonySecurity->shouldReceive('isGranted')
            ->once()
            ->with('IS_AUTHENTICATED_REMEMBERED', null)
            ->andReturnTrue();

        $result = (new Security($symfonySecurity))->isLoggedIn();

        $this->assertTrue($result);
    }

    public function testIsLoggedInFalse(): void
    {
        $symfonySecurity = \Mockery::mock(
            \Symfony\Component\Security\Core\Security::class,
        );
        $symfonySecurity->shouldReceive('getToken')
            ->once()
            ->andReturn(\Mockery::mock(TokenInterface::class));
        $symfonySecurity->shouldReceive('isGranted')
            ->once()
            ->with('IS_AUTHENTICATED_REMEMBERED', null)
            ->andReturnFalse();

        $result = (new Security($symfonySecurity))->isLoggedIn();

        $this->assertFalse($result);
    }

    public function testHasAdminRoleTrue(): void
    {
        $symfonySecurity = \Mockery::mock(
            \Symfony\Component\Security\Core\Security::class,
        );
        $symfonySecurity->shouldReceive('isGranted')
            ->once()
            ->with('ROLE_ADMIN', null)
            ->andReturnTrue();

        $result = (new Security($symfonySecurity))->hasAdminRole();

        $this->assertTrue($result);
    }

    public function testHasAdminRoleFalse(): void
    {
        $symfonySecurity = \Mockery::mock(
            \Symfony\Component\Security\Core\Security::class,
        );
        $symfonySecurity->shouldReceive('isGranted')
            ->once()
            ->with('ROLE_ADMIN', null)
            ->andReturnFalse();

        $result = (new Security($symfonySecurity))->hasAdminRole();

        $this->assertFalse($result);
    }

    public function testGetToken(): void
    {
        $symfonySecurity = \Mockery::mock(
            \Symfony\Component\Security\Core\Security::class,
        );
        $symfonySecurity->shouldReceive('getToken')
            ->once()
            ->withNoArgs()
            ->andReturn(\Mockery::mock(TokenInterface::class));

        $result = (new Security($symfonySecurity))->getToken();

        $this->assertInstanceOf(TokenInterface::class, $result);
    }

    public function testGetTokenNoToken(): void
    {
        $symfonySecurity = \Mockery::mock(
            \Symfony\Component\Security\Core\Security::class,
        );
        $symfonySecurity->shouldReceive('getToken')
            ->once()
            ->withNoArgs()
            ->andReturnNull();

        $result = (new Security($symfonySecurity))->getToken();

        $this->assertNull($result);
    }
}
