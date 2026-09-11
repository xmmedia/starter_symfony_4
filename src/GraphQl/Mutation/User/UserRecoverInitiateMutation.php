<?php

declare(strict_types=1);

namespace App\GraphQl\Mutation\User;

use App\Model\User\Command\InitiatePasswordRecovery;
use App\Projection\User\UserFinder;
use App\Security\Security;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\TooManyPasswordRequestsException;
use Xm\SymfonyBundle\Infrastructure\GraphQl\Error\NotFoundError;
use Xm\SymfonyBundle\Infrastructure\GraphQl\Error\TooManyRequestsError;
use Xm\SymfonyBundle\Model\Email;

final readonly class UserRecoverInitiateMutation implements MutationInterface
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
            throw new NotFoundError('Logged in users cannot change their password this way.');
        }

        if (!$this->testing) {
            // @codeCoverageIgnoreStart
            sleep(random_int(0, 3));
            // @codeCoverageIgnoreEnd
        }

        $user = $this->userFinder->findOneByEmail(
            Email::fromString(mb_strtolower($args['email'])),
        );

        if (!$user || !$user->active()) {
            throw new NotFoundError('An account with that email cannot be found.');
        }

        try {
            $this->commandBus->dispatch(
                InitiatePasswordRecovery::now($user->userId(), $user->email()),
            );
        } catch (TooManyPasswordRequestsException $e) {
            throw new TooManyRequestsError('Too many password resets have been requested', $e);
        }

        return [
            'success' => true,
        ];
    }
}
