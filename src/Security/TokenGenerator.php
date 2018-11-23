<?php

declare(strict_types=1);

namespace App\Security;

class TokenGenerator
{
    public function __invoke()
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }
}
