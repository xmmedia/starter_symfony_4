<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Mutation\User;

use App\Model\User\Command\ActivateUserByAdmin;
use App\Model\User\Command\DeactivateUserByAdmin;
use App\Model\User\UserId;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class AdminUserActivateMutation implements MutationInterface
{
    public function __construct(private readonly MessageBusInterface $commandBus)
    {
    }

    public function __invoke(Argument $args): array
    {
        $userId = UserId::fromString($args['user']['userId']);
        $action = strtolower($args['user']['action']);

        $command = match ($action) {
            'activate'   => ActivateUserByAdmin::class,
            'deactivate' => DeactivateUserByAdmin::class,
            default      => throw new UserError(sprintf('The "%s" action is invalid.', $action)),
        };

        $this->commandBus->dispatch($command::user($userId));

        return [
            'userId' => $userId,
        ];
    }
}
