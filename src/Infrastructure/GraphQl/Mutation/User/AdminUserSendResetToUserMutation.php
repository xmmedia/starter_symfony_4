<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Mutation\User;

use App\Model\User\Command\InitiatePasswordRecovery;
use App\Model\User\UserId;
use App\Projection\User\UserFinder;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Messenger\MessageBusInterface;

class AdminUserSendResetToUserMutation implements MutationInterface
{
    private MessageBusInterface $commandBus;
    private UserFinder $userFinder;

    public function __construct(
        MessageBusInterface $commandBus,
        UserFinder $userFinder,
    ) {
        $this->commandBus = $commandBus;
        $this->userFinder = $userFinder;
    }

    public function __invoke(string $userId): array
    {
        $userId = UserId::fromString($userId);

        $user = $this->userFinder->find($userId);
        if (!$user) {
            throw new UserError('The user could not be found.');
        }

        $this->commandBus->dispatch(
            InitiatePasswordRecovery::now($user->userId(), $user->email()),
        );

        return [
            'userId' => $userId,
        ];
    }
}
