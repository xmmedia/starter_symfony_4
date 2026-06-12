<?php

declare(strict_types=1);

namespace App\Security;

use App\Infrastructure\Service\DefaultRouteProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

/**
 * Returns a JSON redirect response for passkey login (which is AJAX-based).
 * The frontend JS reads the redirect URL and performs window.location.href navigation.
 */
final class PasskeySuccessHandler implements AuthenticationSuccessHandlerInterface
{
    use TargetPathTrait;

    private string $firewallName = 'main';

    public function __construct(
        private readonly RouterInterface $router,
        private readonly DefaultRouteProvider $defaultRoute,
    ) {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        return new JsonResponse(['redirect' => $this->determineTargetUrl($request)]);
    }

    public function setFirewallName(string $firewallName): void
    {
        $this->firewallName = $firewallName;
    }

    private function determineTargetUrl(Request $request): string
    {
        if ($targetUrl = $this->getTargetPath($request->getSession(), $this->firewallName)) {
            $this->removeTargetPath($request->getSession(), $this->firewallName);

            return $targetUrl;
        }

        return $this->router->generate(...($this->defaultRoute)());
    }
}
