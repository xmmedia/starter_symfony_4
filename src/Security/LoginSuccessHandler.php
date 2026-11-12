<?php

declare(strict_types=1);

namespace App\Security;

use App\Infrastructure\Service\DefaultRouteProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * Symfony's handler, with the default route depending on the user & only redirecting to this site.
 */
class LoginSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    public function __construct(
        private readonly RouterInterface $router,
        HttpUtils $httpUtils,
        private readonly DefaultRouteProvider $defaultRoute,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct($httpUtils, [], $logger);
    }

    protected function determineTargetUrl(Request $request): string
    {
        // depends on the signed in user's roles, so it can't be set in config
        $this->options['default_target_path'] = $this->router->generate(...($this->defaultRoute)());

        $targetUrl = parent::determineTargetUrl($request);
        if ($this->isOwnUrl($request, $targetUrl)) {
            return $targetUrl;
        }

        $this->logger?->debug(\sprintf('Ignoring target path "%s": not on this site.', $targetUrl));

        return $this->options['default_target_path'];
    }

    /**
     * The target can come from the query string, so anyone can put anything in it: only a path or an
     * https URL on this site is followed. "//example.com" & "/\example.com" aren't paths we link to, so
     * they're turned down too (HttpUtils would keep them on this site anyway).
     */
    private function isOwnUrl(Request $request, string $targetUrl): bool
    {
        if (str_starts_with($targetUrl, '/')
            && !str_starts_with($targetUrl, '//')
            && !str_starts_with($targetUrl, '/\\')
        ) {
            return true;
        }

        return str_starts_with($targetUrl, 'https://'.$request->getHttpHost().'/');
    }
}
