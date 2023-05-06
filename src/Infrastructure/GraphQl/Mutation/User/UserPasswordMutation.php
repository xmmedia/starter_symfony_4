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

final readonly class UserPasswordMutation implements MutationInterface
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly PasswordHasher $passwordHasher,
        private readonly Security $security,
        private readonly ?PasswordStrengthInterface $passwordStrength = null,
        private readonly ?HttpClientInterface $pwnedHttpClient = null,
    ) {
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
