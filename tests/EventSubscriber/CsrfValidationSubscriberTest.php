<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\EventSubscriber\CsrfValidationSubscriber;
use App\Tests\BaseTestCase;
use Mockery;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
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
            $subscribed[key($subscribed)][1]
        );
        next($subscribed);
        $this->assertEquals(
            0,
            $subscribed[key($subscribed)][1]
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
        $event->shouldReceive('getRequestType')
            ->once()
            ->andReturn(HttpKernelInterface::MASTER_REQUEST);

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
        $event->shouldReceive('getRequestType')
            ->once()
            ->andReturn(HttpKernelInterface::MASTER_REQUEST);

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
        $event->shouldReceive('getRequestType')
            ->once()
            ->andReturn(HttpKernelInterface::MASTER_REQUEST);

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
        $event->shouldReceive('getRequestType')
            ->once()
            ->andReturn(HttpKernelInterface::MASTER_REQUEST);

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
        $event->shouldReceive('getRequestType')
            ->once()
            ->andReturn(HttpKernelInterface::MASTER_REQUEST);

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
        $event->shouldReceive('getRequestType')
            ->once()
            ->andReturn(HttpKernelInterface::SUB_REQUEST);

        $tokenManager = Mockery::mock(CsrfTokenManagerInterface::class);

        $subscriber = new CsrfValidationSubscriber($tokenManager);

        $subscriber->{$method}($event);
    }

    public function testAddCsrfToken(): void
    {
        $subscribed = CsrfValidationSubscriber::getSubscribedEvents();
        $method = $subscribed[KernelEvents::RESPONSE][0];

        $response = Mockery::mock(Response::class);
        $response->headers = Mockery::mock(ResponseHeaderBag::class);
        $response->headers->shouldReceive('setCookie')
            ->once()
            ->withArgs(function ($args): bool {
                if (!$args instanceof Cookie) {
                    return false;
                }

                if ('CSRF-TOKEN' !== $args->getName()) {
                    return false;
                }

                if ('token' !== $args->getValue()) {
                    return false;
                }

                if (0 !== $args->getExpiresTime()) {
                    return false;
                }

                if (!$args->isSecure()) {
                    return false;
                }

                return true;
            });

        $event = Mockery::mock(ResponseEvent::class);
        $event->shouldReceive('getResponse')
            ->atLeast()
            ->once()
            ->andReturn($response);

        $tokenManager = Mockery::mock(CsrfTokenManagerInterface::class);
        $tokenManager->shouldReceive('getToken')
            ->once()
            ->andReturn(new CsrfToken('main', 'token'));

        $subscriber = new CsrfValidationSubscriber($tokenManager);

        $subscriber->{$method}($event);
    }
}
