<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Mutation\User;

use App\Model\Email;
use App\Model\User\Command\AdminAddUser;
use App\Model\User\Name;
use App\Model\User\Role;
use App\Model\User\UserId;
use App\Security\PasswordEncoder;
use App\Security\TokenGenerator;
use App\Util\Assert;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class AdminUserAddMutation implements MutationInterface
{
    /** @var MessageBusInterface */
    private $commandBus;

    /** @var TokenGenerator */
    private $tokenGenerator;

    /** @var PasswordEncoder */
    private $passwordEncoder;

    public function __construct(
        MessageBusInterface $commandBus,
        TokenGenerator $tokenGenerator,
        PasswordEncoder $passwordEncoder
    ) {
        $this->commandBus = $commandBus;
        $this->tokenGenerator = $tokenGenerator;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function __invoke(Argument $args): array
    {
        $userId = UserId::fromString($args['user']['userId']);

        if (!$args['user']['setPassword']) {
            $password = ($this->tokenGenerator)()->toString();
        } else {
            $password = $args['user']['password'];
            // password checked here because it's encoded in the command
            Assert::passwordLength($password);
        }

        $email = Email::fromString($args['user']['email']);
        $role = Role::byValue($args['user']['role']);

        $this->commandBus->dispatch(AdminAddUser::with(
            $userId,
            $email,
            ($this->passwordEncoder)($role, $password),
            $role,
            $args['user']['active'],
            Name::fromString($args['user']['firstName']),
            Name::fromString($args['user']['lastName']),
            $args['user']['sendInvite'],
        ));

        return [
            'userId' => $userId,
            'email'  => $email,
            'active' => $args['user']['active'],
        ];
    }
}
