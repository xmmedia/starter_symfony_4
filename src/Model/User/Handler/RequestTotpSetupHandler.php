<?php

declare(strict_types=1);

namespace App\Model\User\Handler;

use App\Model\User\Command\RequestTotpSetup;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\UserList;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;

final readonly class RequestTotpSetupHandler
{
    public function __construct(
        private UserList $userRepo,
        private TotpAuthenticatorInterface $totpAuthenticator,
    ) {
    }

    public function __invoke(RequestTotpSetup $command): void
    {
        $user = $this->userRepo->get($command->userId());

        if (!$user) {
            throw UserNotFound::withUserId($command->userId());
        }

        $secret = $this->totpAuthenticator->generateSecret();

        $user->requestTotpSetup($secret);

        $this->userRepo->save($user);
    }
}
