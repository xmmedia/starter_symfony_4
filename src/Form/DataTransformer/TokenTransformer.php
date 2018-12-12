<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use App\Model\User\Token;
use Symfony\Component\Form\DataTransformerInterface;

class TokenTransformer implements DataTransformerInterface
{
    /**
     * From Token to form value.
     *
     * @param Token|null|string $token
     */
    public function transform($token): ?string
    {
        if (null === $token) {
            return null;
        }

        return $token->toString();
    }

    /**
     * From user input to Token.
     *
     * @param string|null $role
     */
    public function reverseTransform($token): ?Token
    {
        if (null === $token) {
            return null;
        }

        return Token::fromString($token);
    }
}
