<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestInfoProvider
{
    /** @var RequestStack */
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function currentRequest(): Request
    {
        return $this->requestStack->getCurrentRequest();
    }

    public function userAgent(): ?string
    {
        return $this->currentRequest()->headers->get('User-Agent');
    }

    public function ipAddress(): ?string
    {
        return $this->currentRequest()->getClientIp();
    }
}
