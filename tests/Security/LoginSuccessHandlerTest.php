<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Infrastructure\Service\DefaultRouteProvider;
use App\Security\LoginSuccessHandler;
use App\Security\Security;
use App\Tests\BaseTestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\HttpUtils;

class LoginSuccessHandlerTest extends BaseTestCase
{
    private function createDefaultRouteProvider(array $returnValue): DefaultRouteProvider
    {
        $security = \Mockery::mock(Security::class);

        if ($returnValue === ['app_login']) {
            $security->shouldReceive('isLoggedIn')->andReturn(false);
        } elseif ($returnValue === ['admin_default']) {
            $security->shouldReceive('isLoggedIn')->andReturn(true);
            $security->shouldReceive('hasAdminRole')->andReturn(true);
        } else {
            $security->shouldReceive('isLoggedIn')->andReturn(true);
            $security->shouldReceive('hasAdminRole')->andReturn(false);
        }

        return new DefaultRouteProvider($security);
    }

    public function testOnAuthenticationSuccessWithTargetPathParameter(): void
    {
        $targetPath = '/admin/users';
        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('get')
            ->once()
            ->with('_target_path')
            ->andReturn($targetPath);

        $httpUtils = \Mockery::mock(HttpUtils::class);
        $httpUtils->shouldReceive('createRedirectResponse')
            ->once()
            ->with($request, $targetPath)
            ->andReturn(new RedirectResponse($targetPath));

        $defaultRoute = $this->createDefaultRouteProvider(['app_login']);

        $handler = new LoginSuccessHandler(\Mockery::mock(RouterInterface::class), $httpUtils, $defaultRoute);
        $handler->setFirewallName('main');

        $result = $handler->onAuthenticationSuccess($request, \Mockery::mock(TokenInterface::class));

        $this->assertInstanceOf(RedirectResponse::class, $result);
    }

    public function testOnAuthenticationSuccessWithHttpTargetPathParameter(): void
    {
        $targetPath = 'https://example.com/dashboard';
        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('get')
            ->once()
            ->with('_target_path')
            ->andReturn($targetPath);

        $httpUtils = \Mockery::mock(HttpUtils::class);
        $httpUtils->shouldReceive('createRedirectResponse')
            ->once()
            ->with($request, $targetPath)
            ->andReturn(new RedirectResponse($targetPath));

        $defaultRoute = $this->createDefaultRouteProvider(['app_login']);

        $handler = new LoginSuccessHandler(\Mockery::mock(RouterInterface::class), $httpUtils, $defaultRoute);
        $handler->setFirewallName('main');

        $result = $handler->onAuthenticationSuccess($request, \Mockery::mock(TokenInterface::class));

        $this->assertInstanceOf(RedirectResponse::class, $result);
    }

    public function testOnAuthenticationSuccessWithInvalidTargetPathParameter(): void
    {
        $invalidTargetPath = 'javascript:alert(1)';
        $sessionTargetPath = '/user/dashboard';

        $session = \Mockery::mock(SessionInterface::class);
        $session->shouldReceive('get')
            ->once()
            ->with('_security.main.target_path')
            ->andReturn($sessionTargetPath);
        $session->shouldReceive('remove')
            ->once()
            ->with('_security.main.target_path');

        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('get')
            ->once()
            ->with('_target_path')
            ->andReturn($invalidTargetPath);
        $request->shouldReceive('getSession')
            ->times(2)
            ->andReturn($session);

        $router = \Mockery::mock(RouterInterface::class);

        $httpUtils = \Mockery::mock(HttpUtils::class);
        $httpUtils->shouldReceive('createRedirectResponse')
            ->once()
            ->with($request, $sessionTargetPath)
            ->andReturn(new RedirectResponse($sessionTargetPath));

        $defaultRoute = $this->createDefaultRouteProvider(['user_default', ['path' => 'dashboard']]);

        $logger = \Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('debug')
            ->once()
            ->with('Ignoring query parameter "_target_path": not a valid URL.');

        $handler = new LoginSuccessHandler($router, $httpUtils, $defaultRoute, $logger);
        $handler->setFirewallName('main');

        $result = $handler->onAuthenticationSuccess($request, \Mockery::mock(TokenInterface::class));

        $this->assertInstanceOf(RedirectResponse::class, $result);
    }

    public function testOnAuthenticationSuccessWithSessionTargetPath(): void
    {
        $sessionTargetPath = '/user/profile';

        $session = \Mockery::mock(SessionInterface::class);
        $session->shouldReceive('get')
            ->once()
            ->with('_security.main.target_path')
            ->andReturn($sessionTargetPath);
        $session->shouldReceive('remove')
            ->once()
            ->with('_security.main.target_path');

        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('get')
            ->once()
            ->with('_target_path')
            ->andReturnNull();
        $request->shouldReceive('getSession')
            ->times(2)
            ->andReturn($session);

        $httpUtils = \Mockery::mock(HttpUtils::class);
        $httpUtils->shouldReceive('createRedirectResponse')
            ->once()
            ->with($request, $sessionTargetPath)
            ->andReturn(new RedirectResponse($sessionTargetPath));

        $defaultRoute = $this->createDefaultRouteProvider(['user_default', ['path' => 'dashboard']]);

        $handler = new LoginSuccessHandler(\Mockery::mock(RouterInterface::class), $httpUtils, $defaultRoute);
        $handler->setFirewallName('main');

        $result = $handler->onAuthenticationSuccess($request, \Mockery::mock(TokenInterface::class));

        $this->assertInstanceOf(RedirectResponse::class, $result);
    }

    public function testOnAuthenticationSuccessWithDefaultRoute(): void
    {
        $defaultRouteName = 'user_default';
        $defaultRouteParams = ['path' => 'dashboard'];
        $generatedUrl = '/user/dashboard';

        $session = \Mockery::mock(SessionInterface::class);
        $session->shouldReceive('get')
            ->once()
            ->with('_security.main.target_path')
            ->andReturnNull();

        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('get')
            ->once()
            ->with('_target_path')
            ->andReturnNull();
        $request->shouldReceive('getSession')
            ->once()
            ->andReturn($session);

        $router = \Mockery::mock(RouterInterface::class);
        $router->shouldReceive('generate')
            ->once()
            ->with($defaultRouteName, $defaultRouteParams)
            ->andReturn($generatedUrl);

        $httpUtils = \Mockery::mock(HttpUtils::class);
        $httpUtils->shouldReceive('createRedirectResponse')
            ->once()
            ->with($request, $generatedUrl)
            ->andReturn(new RedirectResponse($generatedUrl));

        $defaultRoute = $this->createDefaultRouteProvider([$defaultRouteName, $defaultRouteParams]);

        $handler = new LoginSuccessHandler($router, $httpUtils, $defaultRoute);
        $handler->setFirewallName('main');

        $result = $handler->onAuthenticationSuccess($request, \Mockery::mock(TokenInterface::class));

        $this->assertInstanceOf(RedirectResponse::class, $result);
    }

    public function testOnAuthenticationSuccessWithDefaultRouteNoParams(): void
    {
        $defaultRouteName = 'app_login';
        $generatedUrl = '/login';

        $session = \Mockery::mock(SessionInterface::class);
        $session->shouldReceive('get')
            ->once()
            ->with('_security.main.target_path')
            ->andReturnNull();

        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('get')
            ->once()
            ->with('_target_path')
            ->andReturnNull();
        $request->shouldReceive('getSession')
            ->once()
            ->andReturn($session);

        $router = \Mockery::mock(RouterInterface::class);
        $router->shouldReceive('generate')
            ->once()
            ->with($defaultRouteName)
            ->andReturn($generatedUrl);

        $httpUtils = \Mockery::mock(HttpUtils::class);
        $httpUtils->shouldReceive('createRedirectResponse')
            ->once()
            ->with($request, $generatedUrl)
            ->andReturn(new RedirectResponse($generatedUrl));

        $defaultRoute = $this->createDefaultRouteProvider([$defaultRouteName]);

        $handler = new LoginSuccessHandler($router, $httpUtils, $defaultRoute);
        $handler->setFirewallName('main');

        $result = $handler->onAuthenticationSuccess($request, \Mockery::mock(TokenInterface::class));

        $this->assertInstanceOf(RedirectResponse::class, $result);
    }

    public function testSetFirewallName(): void
    {
        $httpUtils = \Mockery::mock(HttpUtils::class);
        $defaultRoute = $this->createDefaultRouteProvider(['admin_default']);

        $handler = new LoginSuccessHandler(\Mockery::mock(RouterInterface::class), $httpUtils, $defaultRoute);
        $handler->setFirewallName('custom_firewall');

        // Test that the firewall name is used in session key lookup
        $sessionTargetPath = '/admin';

        $session = \Mockery::mock(SessionInterface::class);
        $session->shouldReceive('get')
            ->once()
            ->with('_security.custom_firewall.target_path')
            ->andReturn($sessionTargetPath);
        $session->shouldReceive('remove')
            ->once()
            ->with('_security.custom_firewall.target_path');

        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('get')
            ->once()
            ->with('_target_path')
            ->andReturnNull();
        $request->shouldReceive('getSession')
            ->times(2)
            ->andReturn($session);

        $httpUtils->shouldReceive('createRedirectResponse')
            ->once()
            ->with($request, $sessionTargetPath)
            ->andReturn(new RedirectResponse($sessionTargetPath));

        $result = $handler->onAuthenticationSuccess($request, \Mockery::mock(TokenInterface::class));

        $this->assertInstanceOf(RedirectResponse::class, $result);
    }
}
