<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Mutation\User;

use App\Model\User\Command\ChangePassword;
use App\Security\PasswordHasher;
use App\Security\Security;
use App\Util\Assert;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Xm\SymfonyBundle\Util\PasswordStrengthInterface;
use Xm\SymfonyBundle\Util\StringUtil;

class UserPasswordMutation implements MutationInterface
{
    private MessageBusInterface $commandBus;
    private UserPasswordHasherInterface $userPasswordHasher;
    private PasswordHasher $passwordHasher;
    private Security $security;
    private ?PasswordStrengthInterface $passwordStrength;
    private ?HttpClientInterface $pwnedHttpClient;

    public function __construct(
        MessageBusInterface $commandBus,
        UserPasswordHasherInterface $userPasswordHasher,
        PasswordHasher $passwordHasher,
        Security $security,
        PasswordStrengthInterface $passwordStrength = null,
        HttpClientInterface $pwnedHttpClient = null,
    ) {
        $this->commandBus = $commandBus;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->passwordHasher = $passwordHasher;
        $this->security = $security;
        $this->passwordStrength = $passwordStrength;
        $this->pwnedHttpClient = $pwnedHttpClient;
    }

    public function __invoke(Argument $args): array
    {
        $user = $this->security->getUser();

        $currentPassword = $args['user']['currentPassword'];
        $newPassword = $args['user']['newPassword'];

        // check current password
        Assert::notEmpty(
            // trim to check for empty, but keep for check
            StringUtil::trim($currentPassword),
            'Current password cannot be empty.',
        );
        Assert::true(
            $this->userPasswordHasher->isPasswordValid($user, $currentPassword),
            'Current password does not match.',
        );

        Assert::passwordAllowed(
            $newPassword,
            $user->email(),
            $user->firstName(),
            $user->lastName(),
            null,
            $this->passwordStrength,
            $this->pwnedHttpClient,
        );

        $hashedPassword = ($this->passwordHasher)(
            $user->firstRole(),
            $newPassword,
        );

        $this->commandBus->dispatch(
            ChangePassword::forUser(
                $user->userId(),
                $hashedPassword,
            ),
        );

        return [
            'success' => true,
        ];
    }
}
