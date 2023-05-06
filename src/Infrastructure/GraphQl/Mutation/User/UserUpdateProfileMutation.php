<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Mutation\User;

use App\Model\User\Command\UpdateUserProfile;
use App\Model\User\Name;
use App\Security\Security;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Xm\SymfonyBundle\Model\Email;

final readonly class UserUpdateProfileMutation implements MutationInterface
{
    public function __construct(private readonly MessageBusInterface $commandBus, private readonly Security $security)
    {
    }

    public function __invoke(Argument $args): array
    {
        $this->commandBus->dispatch(
            UpdateUserProfile::with(
                $this->security->getUser()->userId(),
                ...$this->transformData($args['user']),
            ),
        );

        return [
            'success' => true,
        ];
    }

    private function transformData(array $data): array
    {
        return [
            Email::fromString($data['email']),
            Name::fromString($data['firstName']),
            Name::fromString($data['lastName']),
        ];
    }
}
