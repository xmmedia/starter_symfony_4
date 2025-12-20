<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Service;

use App\Infrastructure\Service\DefaultRouteProvider;
use App\Security\Security;
use App\Tests\BaseTestCase;

class DefaultRouteProviderTest extends BaseTestCase
{
    public function testNotLoggedIn(): void
    {
        $security = \Mockery::mock(Security::class);
        $security->shouldReceive('isLoggedIn')
            ->once()
            ->andReturnFalse();

        $this->assertEquals(['app_login'], new DefaultRouteProvider($security)());
    }

    public function testAdminUser(): void
    {
        $security = \Mockery::mock(Security::class);
        $security->shouldReceive('isLoggedIn')
            ->once()
            ->andReturnTrue();
        $security->shouldReceive('hasAdminRole')
            ->once()
            ->andReturnTrue();

        $this->assertEquals(['admin_default'], new DefaultRouteProvider($security)());
    }

    public function testRegularUser(): void
    {
        $security = \Mockery::mock(Security::class);
        $security->shouldReceive('isLoggedIn')
            ->once()
            ->andReturnTrue();
        $security->shouldReceive('hasAdminRole')
            ->once()
            ->andReturnFalse();

        $this->assertEquals(['user_default', ['path' => 'dashboard']], new DefaultRouteProvider($security)());
    }
}
