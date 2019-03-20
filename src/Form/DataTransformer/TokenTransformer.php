<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use App\Model\User\Token;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class TokenTransformer implements DataTransformerInterface
{
    /**
     * From Token to form value.
     *
     * @param Token|string|null $token
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

        try {
            return Token::fromString($token);
        } catch (\InvalidArgumentException $e) {
            throw new TransformationFailedException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
}
