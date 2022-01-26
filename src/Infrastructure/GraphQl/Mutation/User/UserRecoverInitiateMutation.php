<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Mutation\User;

use App\Model\User\Command\InitiatePasswordRecovery;
use App\Projection\User\UserFinder;
use App\Security\Security;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Messenger\MessageBusInterface;
use Xm\SymfonyBundle\Model\Email;

class UserRecoverInitiateMutation implements MutationInterface
{
    private MessageBusInterface $commandBus;
    private UserFinder $userFinder;
    private Security $security;

    public function __construct(
        MessageBusInterface $commandBus,
        UserFinder $userFinder,
        Security $security,
    ) {
        $this->commandBus = $commandBus;
        $this->userFinder = $userFinder;
        $this->security = $security;
    }

    public function __invoke(Argument $args): array
    {
        if ($this->security->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new UserError('Logged in users cannot change their password this way.', 404);
        }

        $user = $this->userFinder->findOneByEmail(
            Email::fromString(mb_strtolower($args['email'])),
        );

        if (!$user || !$user->active()) {
            throw new UserError('An account with that email cannot be found.', 404);
        }

        $this->commandBus->dispatch(
            InitiatePasswordRecovery::now($user->userId(), $user->email()),
        );

        return [
            'success' => true,
        ];
    }
}
