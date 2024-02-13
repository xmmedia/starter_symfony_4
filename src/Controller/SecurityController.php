<?php

declare(strict_types=1);

namespace App\Controller;

use App\Infrastructure\Service\DefaultRouteProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xm\SymfonyBundle\Util\StringUtil;

/**
 * @codeCoverageIgnore
 */
final class SecurityController extends AbstractController
{
    public const TOKEN_SESSION_KEY = 'reset_token';

    #[Route(path: '/login', name: 'app_login')]
    public function login(DefaultRouteProvider $defaultRoute): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute(...$defaultRoute());
        }

        return $this->render('user.html.twig');
    }

    #[Route(path: '/login-link', name: 'app_login_link')]
    public function loginLink(): void
    {
        throw new \LogicException('Shouldn\'t have gotten to the login link action');
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('Shouldn\'t have gotten to the logout action');
    }

    #[Route(path: '/activate/{token}', name: 'user_activate_token', methods: ['GET'])]
    public function activateRedirect(Request $request, string $token): RedirectResponse
    {
        $request->getSession()->set(self::TOKEN_SESSION_KEY, StringUtil::trim($token));

        return $this->redirectToRoute('user_activate');
    }

    #[Route(path: '/verify/{token}', name: 'user_verify_token', methods: ['GET'])]
    public function verifyRedirect(Request $request, string $token): RedirectResponse
    {
        $request->getSession()->set(self::TOKEN_SESSION_KEY, StringUtil::trim($token));

        return $this->redirectToRoute('user_verify');
    }

    #[Route(path: '/recover/reset/{token}', name: 'user_reset_token', methods: ['GET'])]
    public function resetRedirect(Request $request, string $token): RedirectResponse
    {
        $request->getSession()->set(self::TOKEN_SESSION_KEY, StringUtil::trim($token));

        return $this->redirectToRoute('user_reset');
    }
}
