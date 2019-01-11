<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class CsrfValidationSubscriber implements EventSubscriberInterface
{
    private $routes = [
        'overblog_graphql_endpoint',
        'overblog_graphql_batch_endpoint',
        'overblog_graphql_multiple_endpoint',
        'overblog_graphql_batch_multiple_endpoint',
    ];

    private $tokenName = 'main';
    private $cookieName = 'CSRF-TOKEN';
    private $haderName = 'X-CSRF-TOKEN';

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

    public function validateCsrf(GetResponseEvent $event): void
    {
        $request = $event->getRequest();

        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        if (!in_array($request->get('_route'), $this->routes)) {
            return;
        }

        $value = $request->headers->get($this->haderName);

        if (!$value || !$this->csrfTokenManager->isTokenValid(new CsrfToken($this->tokenName, $value))) {
            throw new AccessDeniedHttpException('Bad CSRF token.');
        }
    }

    public function addCsrfCookie(FilterResponseEvent $event): void
    {
        $event->getResponse()->headers->setCookie(
            new Cookie(
                $this->cookieName,
                $this->csrfTokenManager->getToken($this->tokenName)->getValue(),
                0,
                '/',
                null,
                true,
                false
            )
        );
    }
}
