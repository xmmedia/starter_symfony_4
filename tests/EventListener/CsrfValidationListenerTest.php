<?php

declare(strict_types=1);

namespace App\Tests\EventListener;

use App\EventListener\CsrfValidationListener;
use App\Tests\BaseTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\SameOriginCsrfTokenManager;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class CsrfValidationListenerTest extends BaseTestCase
{
    use EventListenerAttributeTrait;

    private const string TOKEN = 'abcdefghijklmnopqrstuvwx';

    public function testListensTo(): void
    {
        $attribute = $this->eventListenerAttribute(CsrfValidationListener::class, 'validateCsrf');

        $this->assertSame(KernelEvents::REQUEST, $attribute->event);
        $this->assertSame(10, $attribute->priority);
    }

    #[DataProvider('allCheckedRoutes')]
    public function testValidateCsrfHeader(string $route): void
    {
        $request = $this->postRequest($route);
        $request->headers->set('Sec-Fetch-Site', 'same-origin');
        $request->headers->set('csrf-token', self::TOKEN);

        $this->validateCsrf($request);
    }

    public static function allCheckedRoutes(): \Generator
    {
        yield ['overblog_graphql_endpoint'];
        yield ['overblog_graphql_batch_endpoint'];
        // @todo-symfony enable if using multiple gql schemas
        // yield ['overblog_graphql_multiple_endpoint'];
        // yield ['overblog_graphql_batch_multiple_endpoint'];
    }

    public function testValidateCsrfOriginOnly(): void
    {
        $request = $this->postRequest('overblog_graphql_endpoint');
        $request->headers->set('Sec-Fetch-Site', 'same-origin');

        $this->validateCsrf($request);
    }

    public function testValidateCsrfCrossOrigin(): void
    {
        $request = $this->postRequest('overblog_graphql_endpoint');
        $request->headers->set('Sec-Fetch-Site', 'cross-site');
        $request->headers->set('csrf-token', self::TOKEN);

        $this->expectException(InvalidCsrfTokenException::class);

        $this->validateCsrf($request);
    }

    public function testValidateCsrfNoOriginNoToken(): void
    {
        $this->expectException(InvalidCsrfTokenException::class);

        $this->validateCsrf($this->postRequest('overblog_graphql_endpoint'));
    }

    public function testValidateCsrfTokenTooShort(): void
    {
        $request = $this->postRequest('overblog_graphql_endpoint');
        $request->headers->set('Sec-Fetch-Site', 'same-origin');
        $request->headers->set('csrf-token', 'token');

        $this->expectException(InvalidCsrfTokenException::class);

        $this->validateCsrf($request);
    }

    public function testValidateCsrfUncheckedRoute(): void
    {
        $this->validateCsrf($this->postRequest('index'));
    }

    public function testValidateCsrfNotPost(): void
    {
        $request = new Request();
        $request->setMethod('GET');

        $this->validateCsrf($request);
    }

    public function testValidateCsrfNotMasterRequest(): void
    {
        $event = \Mockery::mock(RequestEvent::class);
        $event->shouldReceive('getRequest')
            ->atLeast()
            ->once()
            ->andReturn(new Request());
        $event->shouldReceive('isMainRequest')
            ->once()
            ->andReturnFalse();

        new CsrfValidationListener(\Mockery::mock(CsrfTokenManagerInterface::class))
            ->validateCsrf($event);
    }

    private function postRequest(string $route): Request
    {
        $request = new Request([], [], ['_route' => $route]);
        $request->setMethod('POST');

        return $request;
    }

    private function validateCsrf(Request $request): void
    {
        $event = \Mockery::mock(RequestEvent::class);
        $event->shouldReceive('getRequest')
            ->atLeast()
            ->once()
            ->andReturn($request);
        $event->shouldReceive('isMainRequest')
            ->once()
            ->andReturnTrue();

        $requestStack = new RequestStack([$request]);

        // as configured in framework.csrf_protection
        new CsrfValidationListener(
            new SameOriginCsrfTokenManager(
                $requestStack,
                null,
                null,
                ['authenticate', 'graphql'],
                SameOriginCsrfTokenManager::CHECK_HEADER,
            ),
        )->validateCsrf($event);
    }
}
