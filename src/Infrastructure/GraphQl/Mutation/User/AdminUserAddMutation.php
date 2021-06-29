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
    /** @var MessageBusInterface */
    private $commandBus;

    /** @var TokenGenerator */
    private $tokenGenerator;

    /** @var PasswordHasher */
    private $passwordEncoder;

    /** @var PasswordStrengthInterface|null */
    private $passwordStrength;

    /** @var HttpClientInterface|null */
    private $pwnedHttpClient;

    public function __construct(
        MessageBusInterface $commandBus,
        TokenGenerator $tokenGenerator,
        PasswordHasher $passwordEncoder,
        PasswordStrengthInterface $passwordStrength = null,
        HttpClientInterface $pwnedHttpClient = null
    ) {
        $this->commandBus = $commandBus;
        $this->tokenGenerator = $tokenGenerator;
        $this->passwordEncoder = $passwordEncoder;
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
            $this->pwnedHttpClient
        );

        $this->commandBus->dispatch(
            AdminAddUser::with(
                $userId,
                $email,
                ($this->passwordEncoder)($role, $password),
                $role,
                $args['user']['active'],
                $firstName,
                $lastName,
                $args['user']['sendInvite'],
            )
        );

        return [
            'userId' => $userId,
            'email'  => $email,
            'active' => $args['user']['active'],
        ];
    }
}
