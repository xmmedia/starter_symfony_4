<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Mutation;

use App\Model\User\Command\InitiatePasswordRecovery;
use App\Model\User\UserId;
use App\Repository\UserRepository;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Messenger\MessageBusInterface;

class UserSendResetToUserMutation implements MutationInterface
{
    /** @var MessageBusInterface */
    private $commandBus;

    /** @var UserRepository */
    private $userRepo;

    public function __construct(
        MessageBusInterface $commandBus,
        UserRepository $userRepo
    ) {
        $this->commandBus = $commandBus;
        $this->userRepo = $userRepo;
    }

    public function __invoke(Argument $args): array
    {
        $userId = UserId::fromString($args['user']['id']);

        $user = $this->userRepo->find($userId);
        if (!$user) {
            throw new UserError('The user could not be found.');
        }

        $this->commandBus->dispatch(
            InitiatePasswordRecovery::now($user->id(), $user->email())
        );

        return [
            'id' => $userId,
        ];
    }
}
