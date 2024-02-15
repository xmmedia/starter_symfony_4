<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Mutation\User;

use App\Model\User\Command\InitiatePasswordRecovery;
use App\Projection\User\UserFinder;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Messenger\MessageBusInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\TooManyPasswordRequestsException;
use Xm\SymfonyBundle\Model\Email;

final readonly class UserRecoverInitiateMutation implements MutationInterface
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private UserFinder $userFinder,
        private bool $testing = false,
    ) {
    }

    public function __invoke(Argument $args): array
    {
        if (!$this->testing) {
            sleep(random_int(0, 3));
        }

        $user = $this->userFinder->findOneByEmail(
            Email::fromString(mb_strtolower($args['email'])),
        );

        if (!$user || !$user->active()) {
            throw new UserError('An account with that email cannot be found.', 404);
        }

        try {
            $this->commandBus->dispatch(
                InitiatePasswordRecovery::now($user->userId(), $user->email()),
            );
        } catch (TooManyPasswordRequestsException $e) {
            throw new UserError('Too many password resets have been requested', 429, $e);
        }

        return [
            'success' => true,
        ];
    }
}
