<?php

declare(strict_types=1);

namespace App\Security;

use App\Model\User\Token;

class TokenGenerator implements TokenGeneratorInterface
{
    public function __invoke(): Token
    {
        return Token::fromString(
            rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=')
        );
    }
}
