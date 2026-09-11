<?php

declare(strict_types=1);

namespace App\Controller;

use App\Infrastructure\Service\DefaultRouteProvider;
use App\Security\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xm\SymfonyBundle\Security\SessionExpiry;
use Xm\SymfonyBundle\Util\StringUtil;

final class SecurityController extends AbstractController
{
    public const string TOKEN_SESSION_KEY = 'reset_token';

    #[Route(path: '/login', name: 'app_login')]
    public function login(DefaultRouteProvider $defaultRoute): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute(...$defaultRoute());
        }

        return $this->render('user.html.twig');
    }

    #[Route(path: '/login-link', name: 'app_login_link')]
    public function loginLink(): never
    {
        throw new \LogicException('Shouldn\'t have gotten to the login link action');
    }

    /**
     * Who's signed in & how long until their session expires (null if it doesn't). Requests to this
     * route don't count as activity (see SessionExpirySubscriber), so a GET checks without extending
     * the session. A POST extends it.
     */
    #[Route(
        path: '/session-info',
        name: 'app_session_info',
        defaults: [SessionExpiry::EXTEND_ATTRIBUTE => false],
        methods: ['GET', 'POST'],
    )]
    public function sessionInfo(Request $request, Security $security, SessionExpiry $sessionExpiry): JsonResponse
    {
        $user = $security->getUser();

        if ($user && $request->isMethod(Request::METHOD_POST)) {
            $sessionExpiry->extend($request);
        }

        return $this->json([
            'userId'    => $user?->userId()->toString(),
            'remaining' => $sessionExpiry->remaining($request),
        ]);
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
