<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Mutation\User;

use App\Model\User\Command\AdminDeleteUser;
use App\Model\User\UserId;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class AdminUserDeleteMutation implements MutationInterface
{
    public function __construct(private MessageBusInterface $commandBus)
    {
    }

    public function __invoke(string $userId): array
    {
        $this->commandBus->dispatch(
            AdminDeleteUser::now(UserId::fromString($userId)),
        );

        return [
            'success' => true,
        ];
    }
}
