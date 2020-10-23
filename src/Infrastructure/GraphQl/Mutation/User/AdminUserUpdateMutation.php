<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Mutation\User;

use App\Model\User\Command\AdminChangePassword;
use App\Model\User\Command\AdminUpdateUser;
use App\Model\User\Name;
use App\Model\User\Role;
use App\Model\User\UserId;
use App\Security\PasswordEncoder;
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

    /** @var PasswordEncoder */
    private $passwordEncoder;

    /** @var PasswordStrengthInterface|null */
    private $passwordStrength;

    /** @var HttpClientInterface|null */
    private $pwnedHttpClient;

    public function __construct(
        MessageBusInterface $commandBus,
        PasswordEncoder $passwordEncoder,
        PasswordStrengthInterface $passwordStrength = null,
        HttpClientInterface $pwnedHttpClient = null
    ) {
        $this->commandBus = $commandBus;
        $this->passwordEncoder = $passwordEncoder;
        $this->passwordStrength = $passwordStrength;
        $this->pwnedHttpClient = $pwnedHttpClient;
    }

    public function __invoke(Argument $args): array
    {
        $userId = UserId::fromString($args['user']['userId']);

        if ($args['user']['setPassword']) {
            $password = $args['user']['password'];
            // password checked here because it's encoded prior to the command
            Assert::passwordLength($password);
            Assert::passwordComplexity(
                $password,
                [
                    $args['user']['email'],
                    $args['user']['firstName'],
                    $args['user']['lastName'],
                ],
                null,
                $this->passwordStrength
            );
            Assert::compromisedPassword($password, $this->pwnedHttpClient);
        }

        $role = Role::byValue($args['user']['role']);

        $this->commandBus->dispatch(AdminUpdateUser::with(
            $userId,
            Email::fromString($args['user']['email']),
            $role,
            Name::fromString($args['user']['firstName']),
            Name::fromString($args['user']['lastName']),
        ));

        if ($args['user']['setPassword']) {
            $encodedPassword = ($this->passwordEncoder)($role, $password);

            $this->commandBus->dispatch(
                AdminChangePassword::with(
                    $userId,
                    $encodedPassword
                )
            );
        }

        return [
            'userId' => $userId,
        ];
    }
}
