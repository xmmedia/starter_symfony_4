<?php

declare(strict_types=1);

namespace App\GraphQl\Mutation\User;

use App\Model\User\Command\SendActivation;
use App\Model\User\UserId;
use App\Projection\User\UserFinder;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Messenger\MessageBusInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\TooManyPasswordRequestsException;

final readonly class AdminUserSendActivationToUserMutation implements MutationInterface
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private UserFinder $userFinder,
    ) {
    }

    public function __invoke(UserId $userId): array
    {
        $user = $this->userFinder->find($userId);
        if (!$user) {
            throw new UserError('The user could not be found.');
        }

        try {
            $this->commandBus->dispatch(
                SendActivation::now(
                    $user->userId(),
                    $user->email(),
                    $user->firstName(),
                    $user->lastName(),
                ),
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
