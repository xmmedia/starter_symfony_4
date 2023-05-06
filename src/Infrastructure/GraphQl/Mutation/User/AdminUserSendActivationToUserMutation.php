<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Mutation\User;

use App\Model\User\Command\SendActivation;
use App\Model\User\UserId;
use App\Projection\User\UserFinder;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class AdminUserSendActivationToUserMutation implements MutationInterface
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly UserFinder $userFinder,
    ) {
    }

    public function __invoke(string $userId): array
    {
        $userId = UserId::fromString($userId);

        $user = $this->userFinder->find($userId);
        if (!$user) {
            throw new UserError('The user could not be found.');
        }

        $this->commandBus->dispatch(
            SendActivation::now(
                $user->userId(),
                $user->email(),
                $user->firstName(),
                $user->lastName(),
            ),
        );

        return [
            'userId' => $userId,
        ];
    }
}
