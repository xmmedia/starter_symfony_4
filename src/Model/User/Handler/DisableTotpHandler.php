<?php

declare(strict_types=1);

namespace App\Model\User\Handler;

use App\Model\User\Command\DisableTotp;
use App\Model\User\Exception\UserNotFound;
use App\Model\User\UserList;
use App\Projection\User\UserFinder;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final readonly class DisableTotpHandler
{
    public function __construct(
        private UserList $userRepo,
        private UserFinder $userFinder,
        private TotpAuthenticatorInterface $totpAuthenticator,
    ) {
    }

    public function __invoke(DisableTotp $command): void
    {
        $user = $this->userRepo->get($command->userId());

        if (!$user) {
            throw UserNotFound::withUserId($command->userId());
        }

        $userEntity = $this->userFinder->findRefreshed($command->userId()->toString());
        if (null === $userEntity || !$userEntity->isTotpAuthenticationEnabled()) {
            throw new BadRequestHttpException('TOTP is not enabled.');
        }

        if (!$this->totpAuthenticator->checkCode($userEntity, $command->code())) {
            throw new BadRequestHttpException('Invalid TOTP code.');
        }

        $user->disableTotp();

        $this->userRepo->save($user);
    }
}
