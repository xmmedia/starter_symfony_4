<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Mutation;

use App\Model\User\Command\ActivateUserByAdmin;
use App\Model\User\Command\DeactivateUserByAdmin;
use App\Model\User\UserId;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class UserActivateMutation implements MutationInterface
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
        $action = strtolower($args['user']['action']);

        if ('activate' === $action) {
            $command = ActivateUserByAdmin::class;
        } else {
            $command = DeactivateUserByAdmin::class;
        }

        $this->commandBus->dispatch($command::user($userId));

        return [
            'id' => $userId,
        ];
    }
}
