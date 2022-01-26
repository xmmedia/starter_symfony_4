<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Model\User\Exception\InvalidToken;
use App\Model\User\Exception\TokenHasExpired;
use App\Model\User\Token;
use App\Projection\User\UserTokenFinder;

class TokenValidator
{
    private UserTokenFinder $tokenFinder;

    public function __construct(UserTokenFinder $tokenFinder)
    {
        $this->tokenFinder = $tokenFinder;
    }

    public function validate(Token $token): User
    {
        // the token is the ID (a unique key)
        $tokenEntity = $this->tokenFinder->find($token->toString());

        if (!$tokenEntity) {
            throw InvalidToken::tokenDoesntExist($token);
        }

        // there will be a PHP error if the user doesn't exist
        // that's attached to the token
        $user = $tokenEntity->user();

        if (!$user->active()) {
            throw InvalidToken::userInactive($token);
        }

        if ($tokenEntity->generatedAt() < new \DateTimeImmutable('-24 hours')) {
            throw TokenHasExpired::before($token, '24 hours');
        }

        return $user;
    }
}
