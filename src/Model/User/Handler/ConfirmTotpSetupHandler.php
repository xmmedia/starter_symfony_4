<?php

declare(strict_types=1);

namespace App\Model\User\Handler;

use App\Model\User\Command\ConfirmTotpSetup;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\UserList;
use App\Projection\User\UserFinder;
use App\Security\TotpPendingUser;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final readonly class ConfirmTotpSetupHandler
{
    public function __construct(
        private UserList $userRepo,
        private UserFinder $userFinder,
        private TotpAuthenticatorInterface $totpAuthenticator,
    ) {
    }

    public function __invoke(ConfirmTotpSetup $command): void
    {
        $user = $this->userRepo->get($command->userId());

        if (!$user) {
            throw UserNotFound::withUserId($command->userId());
        }

        $userEntity = $this->userFinder->findRefreshed($command->userId()->toString());
        $pendingSecret = $userEntity?->totpPendingSecret();
        if (null === $pendingSecret) {
            throw new BadRequestHttpException('No pending TOTP setup found.');
        }

        // Verify against the pending (not yet active) secret.
        $pendingUser = new TotpPendingUser($userEntity->getUserIdentifier(), $pendingSecret);

        if (!$this->totpAuthenticator->checkCode($pendingUser, $command->code())) {
            throw new BadRequestHttpException('Invalid TOTP code.');
        }

        $user->confirmTotpSetup();

        $this->userRepo->save($user);
    }
}
