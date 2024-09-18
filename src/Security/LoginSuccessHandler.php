<?php

declare(strict_types=1);

namespace App\Security;

use App\Infrastructure\Service\DefaultRouteProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\ParameterBagUtils;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    use TargetPathTrait;

    private string $firewallName;

    public function __construct(
        private readonly RouterInterface $router,
        private readonly HttpUtils $httpUtils,
        private readonly DefaultRouteProvider $defaultRoute,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        return $this->httpUtils->createRedirectResponse($request, $this->determineTargetUrl($request));
    }

    public function setFirewallName(string $firewallName): void
    {
        $this->firewallName = $firewallName;
    }

    protected function determineTargetUrl(Request $request): string
    {
        $targetUrl = ParameterBagUtils::getRequestParameterValue($request, '_target_path');

        if (\is_string($targetUrl) && (str_starts_with($targetUrl, '/') || str_starts_with($targetUrl, 'http'))) {
            return $targetUrl;
        }

        if ($this->logger && $targetUrl) {
            $this->logger->debug(\sprintf('Ignoring query parameter "%s": not a valid URL.', '_target_path'));
        }

        if ($targetUrl = $this->getTargetPath($request->getSession(), $this->firewallName)) {
            $this->removeTargetPath($request->getSession(), $this->firewallName);

            return $targetUrl;
        }

        return $this->router->generate(...($this->defaultRoute)());
    }
}
