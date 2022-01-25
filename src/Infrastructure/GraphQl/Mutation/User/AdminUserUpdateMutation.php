<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Mutation\User;

use App\Model\User\Command\AdminChangePassword;
use App\Model\User\Command\AdminUpdateUser;
use App\Model\User\Name;
use App\Model\User\Role;
use App\Model\User\UserId;
use App\Security\PasswordHasher;
use App\Util\Assert;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Util\PasswordStrengthInterface;

class AdminUserUpdateMutation implements MutationInterface
{
    /** @var MessageBusInterface */
    private $commandBus;

    /** @var PasswordHasher */
    private $passwordHasher;

    /** @var PasswordStrengthInterface|null */
    private $passwordStrength;

    /** @var HttpClientInterface|null */
    private $pwnedHttpClient;

    public function __construct(
        MessageBusInterface $commandBus,
        PasswordHasher $passwordHasher,
        PasswordStrengthInterface $passwordStrength = null,
        HttpClientInterface $pwnedHttpClient = null,
    ) {
        $this->commandBus = $commandBus;
        $this->passwordHasher = $passwordHasher;
        $this->passwordStrength = $passwordStrength;
        $this->pwnedHttpClient = $pwnedHttpClient;
    }

    public function __invoke(Argument $args): array
    {
        $userId = UserId::fromString($args['user']['userId']);
        $email = Email::fromString($args['user']['email']);
        $role = Role::byValue($args['user']['role']);
        $firstName = Name::fromString($args['user']['firstName']);
        $lastName = Name::fromString($args['user']['lastName']);

        if ($args['user']['setPassword']) {
            $password = $args['user']['password'];
            // password checked here because it's encoded prior to the command
            Assert::passwordAllowed(
                $password,
                $email,
                $firstName,
                $lastName,
                null,
                $this->passwordStrength,
                $this->pwnedHttpClient,
            );
        }

        $this->commandBus->dispatch(
            AdminUpdateUser::with(
                $userId,
                $email,
                $role,
                $firstName,
                $lastName,
            ),
        );

        if ($args['user']['setPassword']) {
            $this->commandBus->dispatch(
                AdminChangePassword::with(
                    $userId,
                    ($this->passwordHasher)($role, $password),
                ),
            );
        }

        return [
            'userId' => $userId,
        ];
    }
}
