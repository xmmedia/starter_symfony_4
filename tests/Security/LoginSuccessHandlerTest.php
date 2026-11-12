<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Infrastructure\Service\DefaultRouteProvider;
use App\Security\LoginSuccessHandler;
use App\Security\Security;
use App\Tests\BaseTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\HttpUtils;

class LoginSuccessHandlerTest extends BaseTestCase
{
    private const string DEFAULT_URL = '/login';
    private const string TARGET_PATH_KEY = '_security.main.target_path';

    #[DataProvider('ownTargetPathProvider')]
    public function testTargetPathParameter(string $targetPathFormat): void
    {
        $targetPath = \sprintf($targetPathFormat, $this->faker()->slug());

        $this->assertRedirectsTo($targetPath, $this->createLoginRequest($targetPath));
    }

    public static function ownTargetPathProvider(): \Generator
    {
        yield 'path' => ['/admin/%s'];
        yield 'path with query & fragment' => ['/profile/%s?tab=1#section'];
        yield 'absolute' => ['https://localhost/%s'];
    }

    #[DataProvider('otherSiteTargetPathProvider')]
    public function testOtherSiteTargetPathParameterUsesDefaultRoute(string $targetPathFormat): void
    {
        $request = $this->createLoginRequest(\sprintf($targetPathFormat, $this->faker()->domainName()));
        $request->setSession($this->createSession(null));

        $this->assertRedirectsTo(self::DEFAULT_URL, $request);
    }

    public static function otherSiteTargetPathProvider(): \Generator
    {
        yield 'https' => ['https://%s/'];
        yield 'http' => ['http://%s/admin'];
        yield 'this site over http' => ['http://localhost/%s'];
        yield 'protocol relative' => ['//%s'];
        yield 'backslash protocol relative' => ['/\%s'];
        yield 'user info' => ['https://localhost@%s'];
        yield 'backslash user info' => ['https://%s\@localhost'];
        // without a scheme & host, HttpUtils would treat it as a route name & throw
        yield 'http prefixed, not a URL' => ['http%s'];
        yield 'javascript' => ['javascript:alert("%s")'];
    }

    public function testInvalidTargetPathParameterUsesSessionTargetPath(): void
    {
        $sessionTargetPath = '/admin/'.$this->faker()->slug();

        $request = $this->createLoginRequest('javascript:alert(1)');
        $request->setSession($this->createSession($sessionTargetPath));

        $logger = \Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('debug')
            ->once()
            ->with('Ignoring query parameter "_target_path": not a valid URL.');

        $this->assertRedirectsTo($sessionTargetPath, $request, $logger);
    }

    #[DataProvider('ownTargetPathProvider')]
    public function testSessionTargetPath(string $targetPathFormat): void
    {
        $sessionTargetPath = \sprintf($targetPathFormat, $this->faker()->slug());

        $request = $this->createLoginRequest();
        $request->setSession($this->createSession($sessionTargetPath));

        $this->assertRedirectsTo($sessionTargetPath, $request);
    }

    public function testOtherSiteSessionTargetPathUsesDefaultRoute(): void
    {
        $request = $this->createLoginRequest();
        $request->setSession($this->createSession($this->faker()->url()));

        $this->assertRedirectsTo(self::DEFAULT_URL, $request);
    }

    public function testDefaultRoute(): void
    {
        $defaultRouteName = 'user_default';
        $defaultRouteParams = ['path' => 'dashboard'];
        $generatedUrl = '/user/dashboard';

        $request = $this->createLoginRequest();
        $request->setSession($this->createSession(null));

        $router = \Mockery::mock(RouterInterface::class);
        $router->shouldReceive('generate')
            ->once()
            ->with($defaultRouteName, $defaultRouteParams)
            ->andReturn($generatedUrl);

        $handler = new LoginSuccessHandler(
            $router,
            $this->createHttpUtils($request, $generatedUrl),
            $this->createDefaultRouteProvider([$defaultRouteName, $defaultRouteParams]),
        );
        $handler->setFirewallName('main');

        $result = $handler->onAuthenticationSuccess($request, \Mockery::mock(TokenInterface::class));

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame($generatedUrl, $result->getTargetUrl());
    }

    public function testDefaultRouteNoParams(): void
    {
        $defaultRouteName = 'app_login';

        $request = $this->createLoginRequest();
        $request->setSession($this->createSession(null));

        $router = \Mockery::mock(RouterInterface::class);
        $router->shouldReceive('generate')
            ->once()
            ->with($defaultRouteName)
            ->andReturn(self::DEFAULT_URL);

        $handler = new LoginSuccessHandler(
            $router,
            $this->createHttpUtils($request, self::DEFAULT_URL),
            $this->createDefaultRouteProvider([$defaultRouteName]),
        );
        $handler->setFirewallName('main');

        $result = $handler->onAuthenticationSuccess($request, \Mockery::mock(TokenInterface::class));

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame(self::DEFAULT_URL, $result->getTargetUrl());
    }

    public function testSetFirewallName(): void
    {
        $sessionTargetPath = '/admin';

        $session = \Mockery::mock(SessionInterface::class);
        $session->shouldReceive('get')
            ->once()
            ->with('_security.custom_firewall.target_path')
            ->andReturn($sessionTargetPath);
        $session->shouldReceive('remove')
            ->once()
            ->with('_security.custom_firewall.target_path');

        $request = $this->createLoginRequest();
        $request->setSession($session);

        $handler = new LoginSuccessHandler(
            $this->createRouter(),
            $this->createHttpUtils($request, $sessionTargetPath),
            $this->createDefaultRouteProvider(['app_login']),
        );
        $handler->setFirewallName('custom_firewall');

        $result = $handler->onAuthenticationSuccess($request, \Mockery::mock(TokenInterface::class));

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame($sessionTargetPath, $result->getTargetUrl());
    }

    private function assertRedirectsTo(string $expectedUrl, Request $request, ?LoggerInterface $logger = null): void
    {
        $handler = new LoginSuccessHandler(
            $this->createRouter(),
            $this->createHttpUtils($request, $expectedUrl),
            $this->createDefaultRouteProvider(['app_login']),
            $logger,
        );
        $handler->setFirewallName('main');

        $result = $handler->onAuthenticationSuccess($request, \Mockery::mock(TokenInterface::class));

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame($expectedUrl, $result->getTargetUrl());
        if ($request->hasSession()) {
            $this->assertFalse($request->getSession()->has(self::TARGET_PATH_KEY));
        }
    }

    private function createLoginRequest(?string $targetPath = null): Request
    {
        $parameters = [];
        if (null !== $targetPath) {
            $parameters['_target_path'] = $targetPath;
        }

        return Request::create('https://localhost/login_check', Request::METHOD_POST, $parameters);
    }

    private function createSession(?string $targetPath): SessionInterface
    {
        $session = new Session(new MockArraySessionStorage());
        if (null !== $targetPath) {
            $session->set(self::TARGET_PATH_KEY, $targetPath);
        }

        return $session;
    }

    private function createRouter(): RouterInterface
    {
        $router = \Mockery::mock(RouterInterface::class);
        $router->shouldReceive('generate')
            ->with('app_login')
            ->andReturn(self::DEFAULT_URL);

        return $router;
    }

    private function createHttpUtils(Request $request, string $expectedUrl): HttpUtils
    {
        $httpUtils = \Mockery::mock(HttpUtils::class);
        $httpUtils->shouldReceive('createRedirectResponse')
            ->once()
            ->with($request, $expectedUrl)
            ->andReturn(new RedirectResponse($expectedUrl));

        return $httpUtils;
    }

    private function createDefaultRouteProvider(array $returnValue): DefaultRouteProvider
    {
        $security = \Mockery::mock(Security::class);

        if ($returnValue === ['app_login']) {
            $security->shouldReceive('isLoggedIn')->andReturnFalse();
        } elseif ($returnValue === ['admin_default']) {
            $security->shouldReceive('isLoggedIn')->andReturnTrue();
            $security->shouldReceive('hasAdminRole')->andReturnTrue();
        } else {
            $security->shouldReceive('isLoggedIn')->andReturnTrue();
            $security->shouldReceive('hasAdminRole')->andReturnFalse();
        }

        return new DefaultRouteProvider($security);
    }
}
