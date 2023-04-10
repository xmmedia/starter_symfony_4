<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Mutation\User;

use App\Model\User\Command\AdminAddUser;
use App\Model\User\Name;
use App\Model\User\Role;
use App\Model\User\UserId;
use App\Security\PasswordHasher;
use App\Security\TokenGenerator;
use App\Util\Assert;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Util\PasswordStrengthInterface;

class AdminUserAddMutation implements MutationInterface
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private TokenGenerator $tokenGenerator,
        private PasswordHasher $passwordHasher,
        private ?PasswordStrengthInterface $passwordStrength = null,
        private ?HttpClientInterface $pwnedHttpClient = null,
    ) {
    }

    public function __invoke(Argument $args): array
    {
        $userId = UserId::fromString($args['user']['userId']);
        $email = Email::fromString($args['user']['email']);
        $role = $args['user']['role'];
        if (!$role instanceof Role) {
            $role = Role::byValue($role);
        }
        $firstName = Name::fromString($args['user']['firstName']);
        $lastName = Name::fromString($args['user']['lastName']);

        if (!$args['user']['setPassword']) {
            $password = ($this->tokenGenerator)()->toString();
        } else {
            $password = $args['user']['password'];
        }
        // password checked here because it's encoded prior to the command
        // check both generated & user entered,
        // though unlikely generated will be compromised
        Assert::passwordAllowed(
            $password,
            $email,
            $firstName,
            $lastName,
            null,
            $this->passwordStrength,
            $this->pwnedHttpClient,
        );

        $this->commandBus->dispatch(
            AdminAddUser::with(
                $userId,
                $email,
                ($this->passwordHasher)($role, $password),
                $role,
                $args['user']['active'],
                $firstName,
                $lastName,
                $args['user']['sendInvite'],
            ),
        );

        return [
            'userId' => $userId,
            'email'  => $email,
            'active' => $args['user']['active'],
        ];
    }
}
