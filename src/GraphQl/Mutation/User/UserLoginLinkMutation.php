<?php

declare(strict_types=1);

namespace App\GraphQl\Mutation\User;

use App\Model\User\Command\SendLoginLink;
use App\Projection\User\UserFinder;
use App\Security\Security;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Messenger\MessageBusInterface;
use Xm\SymfonyBundle\Model\Email;

final readonly class UserLoginLinkMutation implements MutationInterface
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private UserFinder $userFinder,
        private Security $security,
        private bool $testing = false,
    ) {
    }

    public function __invoke(Argument $args): array
    {
        if ($this->security->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new UserError('Logged in users cannot request a login link', 404);
        }

        if (!$this->testing) {
            sleep(random_int(0, 3));
        }

        $user = $this->userFinder->findOneByEmail(Email::fromString(mb_strtolower($args['email'])));

        // don't let them know if the user exists
        if (!$user || !$user->active()) {
            return [
                'success' => true,
            ];
        }

        $this->commandBus->dispatch(
            SendLoginLink::now($user->userId(), $user->email()),
        );

        return [
            'success' => true,
        ];
    }
}
