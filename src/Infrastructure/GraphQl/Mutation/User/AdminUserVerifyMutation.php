<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Mutation\User;

use App\Model\User\Command\VerifyUserByAdmin;
use App\Model\User\UserId;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class AdminUserVerifyMutation implements MutationInterface
{
    public function __construct(private MessageBusInterface $commandBus)
    {
    }

    public function __invoke(string $userId): array
    {
        $userId = UserId::fromString($userId);

        $this->commandBus->dispatch(VerifyUserByAdmin::now($userId));

        return [
            'userId' => $userId,
        ];
    }
}
