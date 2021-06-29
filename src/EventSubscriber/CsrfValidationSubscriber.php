<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class CsrfValidationSubscriber implements EventSubscriberInterface
{
    private $routes = [
        'app_login',
        'overblog_graphql_endpoint',
        'overblog_graphql_batch_endpoint',
        'overblog_graphql_multiple_endpoint',
        'overblog_graphql_batch_multiple_endpoint',
    ];

    private $tokenName = 'main';
    private $cookieName = 'CSRF-TOKEN';

    /** @var CsrfTokenManagerInterface */
    private $csrfTokenManager;

    public function __construct(CsrfTokenManagerInterface $csrfTokenManager)
    {
        $this->csrfTokenManager = $csrfTokenManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST  => ['validateCsrf', 10],
            KernelEvents::RESPONSE => ['addCsrfCookie', 0],
        ];
    }

    public function validateCsrf(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$event->isMainRequest()) {
            return;
        }

        if ('POST' !== $request->getMethod()) {
            return;
        }

        if (!\in_array($request->get('_route'), $this->routes)) {
            return;
        }

        $value = $request->cookies->get($this->cookieName);
        if (!$value) {
            throw new AccessDeniedHttpException('Bad CSRF token.');
        }

        $token = new CsrfToken($this->tokenName, $value);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new AccessDeniedHttpException('Bad CSRF token.');
        }
    }

    public function addCsrfCookie(ResponseEvent $event): void
    {
        $token = $this->csrfTokenManager->getToken($this->tokenName)->getValue();

        $event->getResponse()->headers->setCookie(
            Cookie::create($this->cookieName, $token, 0, '/', null, true)
        );
    }
}
