<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Model\User\Exception\InvalidToken;
use App\Model\User\Exception\TokenHasExpired;
use App\Model\User\Token;
use App\Repository\UserTokenRepository;

class TokenValidator
{
    /** @var UserTokenRepository */
    private $tokenRepo;

    public function __construct(UserTokenRepository $tokenRepo)
    {
        $this->tokenRepo = $tokenRepo;
    }

    public function validate(Token $token): User
    {
        $tokenEntity = $this->tokenRepo->find($token);

        if (!$tokenEntity) {
            throw InvalidToken::tokenDoesntExist($token);
        }

        if (!$user = $tokenEntity->user()) {
            throw InvalidToken::userDoesntExist($token);
        }

        if (!$user->active()) {
            throw InvalidToken::userInactive($token);
        }

        if ($user->verified()) {
            throw InvalidToken::userVerified($token);
        }

        if ($tokenEntity->generatedAt() < new \DateTimeImmutable('-24 hours')) {
            throw TokenHasExpired::before($token, '24 hours');
        }

        return $user;
    }
}
