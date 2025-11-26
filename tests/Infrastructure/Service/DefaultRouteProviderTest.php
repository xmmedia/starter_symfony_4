<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Service;

use App\Infrastructure\Service\DefaultRouteProvider;
use App\Security\Security;
use App\Tests\BaseTestCase;

class DefaultRouteProviderTest extends BaseTestCase
{
    public function testReturnsLoginRouteWhenNotLoggedIn(): void
    {
        $security = \Mockery::mock(Security::class);
        $security->shouldReceive('isLoggedIn')
            ->once()
            ->andReturn(false);

        $provider = new DefaultRouteProvider($security);

        $result = $provider();

        $this->assertEquals(['app_login'], $result);
    }

    public function testReturnsAdminDefaultRouteForAdminUser(): void
    {
        $security = \Mockery::mock(Security::class);
        $security->shouldReceive('isLoggedIn')
            ->once()
            ->andReturn(true);
        $security->shouldReceive('hasAdminRole')
            ->once()
            ->andReturn(true);

        $provider = new DefaultRouteProvider($security);

        $result = $provider();

        $this->assertEquals(['admin_default'], $result);
    }

    public function testReturnsUserDefaultRouteForRegularUser(): void
    {
        $security = \Mockery::mock(Security::class);
        $security->shouldReceive('isLoggedIn')
            ->once()
            ->andReturn(true);
        $security->shouldReceive('hasAdminRole')
            ->once()
            ->andReturn(false);

        $provider = new DefaultRouteProvider($security);

        $result = $provider();

        $this->assertEquals(['user_default', ['path' => 'dashboard']], $result);
    }

    public function testReturnsUserDefaultRouteForSuperAdmin(): void
    {
        // Super admin also has admin role, so should get admin_default
        $security = \Mockery::mock(Security::class);
        $security->shouldReceive('isLoggedIn')
            ->once()
            ->andReturn(true);
        $security->shouldReceive('hasAdminRole')
            ->once()
            ->andReturn(true);

        $provider = new DefaultRouteProvider($security);

        $result = $provider();

        $this->assertEquals(['admin_default'], $result);
    }
}
