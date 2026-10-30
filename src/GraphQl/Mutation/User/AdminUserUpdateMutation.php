<?php

declare(strict_types=1);

namespace App\GraphQl\Mutation\User;

use App\GraphQl\Error\PasswordNotAllowedError;
use App\Infrastructure\Service\UserPasswordStore;
use App\Model\User\Command\AdminChangePassword;
use App\Model\User\Command\AdminUpdateUser;
use App\Model\User\Name;
use App\Model\User\Role;
use App\Model\User\UserData;
use App\Security\PasswordHasher;
use App\Security\RoleAssignment;
use App\Util\Assert;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Xm\SymfonyBundle\EventSubscriber\GraphQlErrorSubscriber;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Util\PasswordStrengthInterface;

final readonly class AdminUserUpdateMutation implements MutationInterface
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private PasswordHasher $passwordHasher,
        private UserPasswordStore $passwordStore,
        private RoleAssignment $roleAssignment,
        private ?PasswordStrengthInterface $passwordStrength = null,
        private ?HttpClientInterface $pwnedHttpClient = null,
    ) {
    }

    public function __invoke(#[\SensitiveParameter] Argument $args): array
    {
        $userId = $args['user']['userId'];
        $email = Email::fromString($args['user']['email']);
        $role = $args['user']['role'];
        if (!$role instanceof Role) {
            $role = Role::from($role);
        }
        if (!$this->roleAssignment->allowed($role)) {
            throw new UserError(GraphQlErrorSubscriber::ACCESS_DENIED_MESSAGE);
        }
        $firstName = Name::fromString($args['user']['firstName']);
        $lastName = Name::fromString($args['user']['lastName']);

        if ($args['user']['setPassword']) {
            $password = $args['user']['password'];
            // password checked here because it's encoded prior to the command
            try {
                Assert::passwordAllowed(
                    $password,
                    $email,
                    $firstName,
                    $lastName,
                    null,
                    $this->passwordStrength,
                    $this->pwnedHttpClient,
                );
            } catch (\InvalidArgumentException $e) {
                throw new PasswordNotAllowedError($e->getMessage(), $e);
            }
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
            // the hash is deliberately not part of the command
            $this->passwordStore->store($userId, ($this->passwordHasher)($role, $password));

            $this->commandBus->dispatch(
                AdminChangePassword::now($userId),
            );
        }

        return [
            'userId' => $userId,
        ];
    }
}
