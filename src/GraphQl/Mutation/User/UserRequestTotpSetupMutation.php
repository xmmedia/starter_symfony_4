<?php

declare(strict_types=1);

namespace App\GraphQl\Mutation\User;

use App\Model\User\Command\RequestTotpSetup;
use App\Projection\User\UserFinder;
use App\Security\Security;
use App\Security\TotpPendingUser;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class UserRequestTotpSetupMutation implements MutationInterface
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private Security $security,
        private UserFinder $userFinder,
        private TotpAuthenticatorInterface $totpAuthenticator,
    ) {
    }

    public function __invoke(Argument $args): array
    {
        $userId = $this->security->getUser()->userId();

        $this->commandBus->dispatch(RequestTotpSetup::with($userId));

        // Refresh from DB — the projection writes via DBAL, bypassing Doctrine's identity map.
        $userEntity = $this->userFinder->findRefreshed($userId->toString());
        $pendingSecret = $userEntity->totpPendingSecret();

        // Use a temporary wrapper so getQRContent reads the pending (not yet active) secret.
        $pendingUser = new TotpPendingUser($userEntity->getUserIdentifier(), $pendingSecret);

        return [
            'otpauthUrl' => $this->totpAuthenticator->getQRContent($pendingUser),
            'manualKey'  => $pendingSecret,
        ];
    }
}
