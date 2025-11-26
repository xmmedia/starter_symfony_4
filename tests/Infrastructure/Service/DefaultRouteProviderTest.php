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
            ->andReturnFalse();

        $provider = new DefaultRouteProvider($security);

        $this->assertEquals(['app_login'], $provider());
    }

    public function testReturnsAdminDefaultRouteForAdminUser(): void
    {
        $security = \Mockery::mock(Security::class);
        $security->shouldReceive('isLoggedIn')
            ->once()
            ->andReturnTrue();
        $security->shouldReceive('hasAdminRole')
            ->once()
            ->andReturnTrue();

        $provider = new DefaultRouteProvider($security);

        $this->assertEquals(['admin_default'], $provider());
    }

    public function testReturnsUserDefaultRouteForRegularUser(): void
    {
        $security = \Mockery::mock(Security::class);
        $security->shouldReceive('isLoggedIn')
            ->once()
            ->andReturnTrue();
        $security->shouldReceive('hasAdminRole')
            ->once()
            ->andReturnFalse();

        $provider = new DefaultRouteProvider($security);

        $this->assertEquals(['user_default', ['path' => 'dashboard']], $provider());
    }
}
