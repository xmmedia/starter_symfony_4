<?php

declare(strict_types=1);

namespace App\GraphQl\Mutation\User;

use App\Entity\User;
use App\Model\User\Command\SendLoginLink;
use App\Model\User\UserId;
use App\Projection\User\UserFinder;
use JetBrains\PhpStorm\ArrayShape;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class AdminUserSendLoginLinkMutation implements MutationInterface
{
    public function __construct(private MessageBusInterface $commandBus, private UserFinder $userFinder)
    {
    }

    #[ArrayShape(['success' => 'bool', 'user' => User::class | null])]
    public function __invoke(UserId $userId): array
    {
        $user = $this->userFinder->find($userId);
        if (!$user) {
            throw new UserError('The user could not be found.');
        }

        if (!$user->active() || !$user->verified()) {
            return [
                'success' => false,
                'user'    => null,
            ];
        }

        $this->commandBus->dispatch(
            SendLoginLink::now($user->userId(), $user->email()),
        );

        return [
            'success' => true,
            'user'    => $user,
        ];
    }
}
