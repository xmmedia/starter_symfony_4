<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * The GraphQL endpoints aren't forms, so they're not covered by Symfony's CSRF protection.
 * The token id is stateless (see framework.csrf_protection), so validation is based on the
 * origin & the token double submitted in the header by the JS (see csrfToken() in csrf.js).
 * With check_header, the header alone counts as the double submit & the cookie isn't compared:
 * a browser only sends a custom header cross-origin after a CORS preflight, & the app sends no CORS
 * headers (see CORS in AGENTS.md).
 */
class CsrfValidationListener
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
    // the header & the cookie share one name: framework.csrf_protection.cookie_name
    private string $headerName = 'csrf-token';

    public function __construct(private readonly CsrfTokenManagerInterface $csrfTokenManager)
    {
    }

    #[AsEventListener(KernelEvents::REQUEST, priority: 10)]
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
