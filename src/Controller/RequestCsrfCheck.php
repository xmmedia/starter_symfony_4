<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;

trait RequestCsrfCheck
{
    private function checkCsrf(Request $request, string $tokenName): void
    {
        $token = $request->headers->get('x-csrf-token');

        if (!$this->isCsrfTokenValid($tokenName, $token)) {
            throw $this->createAccessDeniedException('The CSRF token is invalid.');
        }
    }
}
