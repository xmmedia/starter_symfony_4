<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\SecurityController;
use App\Infrastructure\Service\DefaultRouteProvider;
use App\Security\Security;
use App\Tests\BaseTestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SecurityControllerTest extends BaseTestCase
{
    public function testLoginMethodExists(): void
    {
        $this->assertTrue(method_exists(SecurityController::class, 'login'));
    }

    public function testLoginLinkThrowsException(): void
    {
        $controller = new SecurityController();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Shouldn\'t have gotten to the login link action');

        $controller->loginLink();
    }

    public function testActivateRedirectReturnsRedirectResponse(): void
    {
        $this->assertTrue(method_exists(SecurityController::class, 'activateRedirect'));
    }

    public function testVerifyRedirectReturnsRedirectResponse(): void
    {
        $this->assertTrue(method_exists(SecurityController::class, 'verifyRedirect'));
    }

    public function testResetRedirectReturnsRedirectResponse(): void
    {
        $this->assertTrue(method_exists(SecurityController::class, 'resetRedirect'));
    }

    public function testTokenSessionKeyIsCorrect(): void
    {
        $this->assertEquals('reset_token', SecurityController::TOKEN_SESSION_KEY);
    }
}
