<?php

declare(strict_types=1);

namespace App\GraphQl\Mutation\User;

use App\Model\User\Command\InitiatePasswordRecovery;
use App\Model\User\UserId;
use App\Projection\User\UserFinder;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Messenger\MessageBusInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\TooManyPasswordRequestsException;

final readonly class AdminUserSendResetToUserMutation implements MutationInterface
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private UserFinder $userFinder,
    ) {
    }

    public function __invoke(string $userId): array
    {
        $userId = UserId::fromString($userId);

        $user = $this->userFinder->find($userId);
        if (!$user) {
            throw new UserError('The user could not be found.');
        }

        try {
            $this->commandBus->dispatch(
                InitiatePasswordRecovery::now($user->userId(), $user->email()),
            );
        } catch (TooManyPasswordRequestsException $e) {
            throw new UserError(
                'Too many password resets have been requested. A password reset can only be requested every hour',
                429,
                $e,
            );
        }

        return [
            'userId' => $userId,
        ];
    }
}
