<?php

declare(strict_types=1);

namespace App\GraphQl\Mutation\User;

use App\Model\User\Command\DisableTotp;
use App\Security\Security;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class UserDisableTotpMutation implements MutationInterface
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private Security $security,
    ) {
    }

    public function __invoke(Argument $args): array
    {
        $this->commandBus->dispatch(
            DisableTotp::with(
                $this->security->getUser()->userId(),
                $args['code'],
            ),
        );

        return ['success' => true];
    }
}
