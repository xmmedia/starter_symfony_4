<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * The GraphQL endpoints aren't forms, so they're not covered by Symfony's CSRF protection.
 * The token id is stateless (see framework.csrf_protection), so validation is based on the
 * origin & the token double submitted in the header by the JS (see csrfToken() in csrf.js).
 */
class CsrfValidationSubscriber implements EventSubscriberInterface
{
    private array $routes = [
        'overblog_graphql_endpoint',
        'overblog_graphql_batch_endpoint',
        // @todo-symfony enable if using multiple gql schemas
        // 'overblog_graphql_multiple_endpoint',
        // 'overblog_graphql_batch_multiple_endpoint',
    ];

    // must be listed in framework.csrf_protection.stateless_token_ids
    private string $tokenId = 'graphql';
    // Symfony checks the header & the cookie under one name: framework.csrf_protection.cookie_name
    private string $headerName = 'csrf-token';

    public function __construct(private readonly CsrfTokenManagerInterface $csrfTokenManager)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['validateCsrf', 10],
        ];
    }

    public function validateCsrf(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$event->isMainRequest()) {
            return;
        }

        if (!$request->isMethod('POST')) {
            return;
        }

        if (!\in_array($request->attributes->get('_route'), $this->routes)) {
            return;
        }

        // without the header the request is validated on the origin alone
        $token = new CsrfToken(
            $this->tokenId,
            $request->headers->get(
                $this->headerName,
                $this->csrfTokenManager->getToken($this->tokenId)->getValue(),
            ),
        );

        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException('Bad CSRF token.');
        }
    }
}
