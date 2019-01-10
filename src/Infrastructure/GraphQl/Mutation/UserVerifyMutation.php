<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Mutation;

use App\Model\User\Command\VerifyUserByAdmin;
use App\Model\User\UserId;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class UserVerifyMutation implements MutationInterface
{
    /** @var MessageBusInterface */
    private $commandBus;

    public function __construct(MessageBusInterface $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function __invoke(Argument $args): array
    {
        $userId = UserId::fromString($args['user']['id']);

        $this->commandBus->dispatch(VerifyUserByAdmin::now($userId));

        return [
            'id' => $userId,
        ];
    }
}
