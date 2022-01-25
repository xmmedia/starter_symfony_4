<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\EventSubscriber\CsrfValidationSubscriber;
use App\Tests\BaseTestCase;
use Mockery;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class CsrfValidationSubscriberTest extends BaseTestCase
{
    public function testSubscribedEvents(): void
    {
        $subscribed = CsrfValidationSubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(KernelEvents::REQUEST, $subscribed);
        $this->assertArrayHasKey(KernelEvents::RESPONSE, $subscribed);

        $this->assertEquals(
            10,
            $subscribed[key($subscribed)][1],
        );
        next($subscribed);
        $this->assertEquals(
            0,
            $subscribed[key($subscribed)][1],
        );
    }

    /**
     * @dataProvider allCheckedRoutes
     */
    public function testValidateCsrf(string $route): void
    {
        $subscribed = CsrfValidationSubscriber::getSubscribedEvents();
        $method = $subscribed[KernelEvents::REQUEST][0];

        $request = new Request([], [], [
            '_route' => $route,
        ], [
            'CSRF-TOKEN' => 'token',
        ]);
        $request->setMethod('POST');

        $event = Mockery::mock(RequestEvent::class);
        $event->shouldReceive('getRequest')
            ->atLeast()
            ->once()
            ->andReturn($request);
        $event->shouldReceive('isMainRequest')
            ->once()
            ->andReturnTrue();

        $tokenManager = Mockery::mock(CsrfTokenManagerInterface::class);
        $tokenManager->shouldReceive('isTokenValid')
            ->once()
            ->andReturnTrue();

        $subscriber = new CsrfValidationSubscriber($tokenManager);

        $subscriber->{$method}($event);
    }

    public function allCheckedRoutes(): \Generator
    {
        yield ['app_login'];
        yield ['overblog_graphql_endpoint'];
        yield ['overblog_graphql_batch_endpoint'];
        yield ['overblog_graphql_multiple_endpoint'];
        yield ['overblog_graphql_batch_multiple_endpoint'];
    }

    public function testValidateCsrfInvalid(): void
    {
        $subscribed = CsrfValidationSubscriber::getSubscribedEvents();
        $method = $subscribed[KernelEvents::REQUEST][0];

        $request = new Request([], [], [
            '_route' => 'app_login',
        ], [
            'CSRF-TOKEN' => 'token',
        ]);
        $request->setMethod('POST');

        $event = Mockery::mock(RequestEvent::class);
        $event->shouldReceive('getRequest')
            ->atLeast()
            ->once()
            ->andReturn($request);
        $event->shouldReceive('isMainRequest')
            ->once()
            ->andReturnTrue();

        $tokenManager = Mockery::mock(CsrfTokenManagerInterface::class);
        $tokenManager->shouldReceive('isTokenValid')
            ->once()
            ->andReturnFalse();

        $subscriber = new CsrfValidationSubscriber($tokenManager);

        $this->expectException(AccessDeniedHttpException::class);

        $subscriber->{$method}($event);
    }

    public function testValidateCsrfNoTokenCookie(): void
    {
        $subscribed = CsrfValidationSubscriber::getSubscribedEvents();
        $method = $subscribed[KernelEvents::REQUEST][0];

        $request = new Request([], [], [
            '_route' => 'app_login',
        ]);
        $request->setMethod('POST');

        $event = Mockery::mock(RequestEvent::class);
        $event->shouldReceive('getRequest')
            ->atLeast()
            ->once()
            ->andReturn($request);
        $event->shouldReceive('isMainRequest')
            ->once()
            ->andReturnTrue();

        $tokenManager = Mockery::mock(CsrfTokenManagerInterface::class);

        $subscriber = new CsrfValidationSubscriber($tokenManager);

        $this->expectException(AccessDeniedHttpException::class);

        $subscriber->{$method}($event);
    }

    public function testValidateCsrfUncheckedRoute(): void
    {
        $subscribed = CsrfValidationSubscriber::getSubscribedEvents();
        $method = $subscribed[KernelEvents::REQUEST][0];

        $request = new Request([], [], [
            '_route' => 'index',
        ]);
        $request->setMethod('POST');

        $event = Mockery::mock(RequestEvent::class);
        $event->shouldReceive('getRequest')
            ->atLeast()
            ->once()
            ->andReturn($request);
        $event->shouldReceive('isMainRequest')
            ->once()
            ->andReturnTrue();

        $tokenManager = Mockery::mock(CsrfTokenManagerInterface::class);

        $subscriber = new CsrfValidationSubscriber($tokenManager);

        $subscriber->{$method}($event);
    }

    public function testValidateCsrfNotPost(): void
    {
        $subscribed = CsrfValidationSubscriber::getSubscribedEvents();
        $method = $subscribed[KernelEvents::REQUEST][0];

        $request = new Request();
        $request->setMethod('GET');

        $event = Mockery::mock(RequestEvent::class);
        $event->shouldReceive('getRequest')
            ->atLeast()
            ->once()
            ->andReturn($request);
        $event->shouldReceive('isMainRequest')
            ->once()
            ->andReturnTrue();

        $tokenManager = Mockery::mock(CsrfTokenManagerInterface::class);

        $subscriber = new CsrfValidationSubscriber($tokenManager);

        $subscriber->{$method}($event);
    }

    public function testValidateCsrfNotMasterRequest(): void
    {
        $subscribed = CsrfValidationSubscriber::getSubscribedEvents();
        $method = $subscribed[KernelEvents::REQUEST][0];

        $request = new Request();

        $event = Mockery::mock(RequestEvent::class);
        $event->shouldReceive('getRequest')
            ->atLeast()
            ->once()
            ->andReturn($request);
        $event->shouldReceive('isMainRequest')
            ->once()
            ->andReturnFalse();

        $tokenManager = Mockery::mock(CsrfTokenManagerInterface::class);

        $subscriber = new CsrfValidationSubscriber($tokenManager);

        $subscriber->{$method}($event);
    }

    public function testAddCsrfToken(): void
    {
        $subscribed = CsrfValidationSubscriber::getSubscribedEvents();
        $method = $subscribed[KernelEvents::RESPONSE][0];

        $kernel = Mockery::mock(HttpKernelInterface::class);
        $request = Request::create('/', 'POST');
        $response = new Response();

        $event = new ResponseEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $response,
        );

        $tokenManager = Mockery::mock(CsrfTokenManagerInterface::class);
        $tokenManager->shouldReceive('getToken')
            ->once()
            ->andReturn(new CsrfToken('main', 'token'));

        $subscriber = new CsrfValidationSubscriber($tokenManager);

        $subscriber->{$method}($event);

        $cookies = $response->headers->getCookies();
        $this->assertCount(1, $cookies);

        $this->assertSame('CSRF-TOKEN', $cookies[0]->getName());
        $this->assertSame('token', $cookies[0]->getValue());
        $this->assertSame(0, $cookies[0]->getExpiresTime());
        $this->assertTrue($cookies[0]->isSecure());
    }
}
