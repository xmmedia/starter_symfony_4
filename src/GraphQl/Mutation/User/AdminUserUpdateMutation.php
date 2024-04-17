<?php

declare(strict_types=1);

namespace App\GraphQl\Mutation\User;

use App\Model\User\Command\AdminChangePassword;
use App\Model\User\Command\AdminUpdateUser;
use App\Model\User\Name;
use App\Model\User\Role;
use App\Model\User\UserData;
use App\Model\User\UserId;
use App\Security\PasswordHasher;
use App\Util\Assert;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Util\PasswordStrengthInterface;

final readonly class AdminUserUpdateMutation implements MutationInterface
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private PasswordHasher $passwordHasher,
        private ?PasswordStrengthInterface $passwordStrength = null,
        private ?HttpClientInterface $pwnedHttpClient = null,
    ) {
    }

    public function __invoke(#[\SensitiveParameter] Argument $args): array
    {
        $userId = UserId::fromString($args['user']['userId']);
        $email = Email::fromString($args['user']['email']);
        $role = $args['user']['role'];
        if (!$role instanceof Role) {
            $role = Role::byValue($role);
        }
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
                UserData::fromArray($args['user']['userData']),
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
