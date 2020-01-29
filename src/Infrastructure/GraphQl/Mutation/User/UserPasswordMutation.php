<?php

declare(strict_types=1);

namespace App\Infrastructure\GraphQl\Mutation\User;

use App\Model\User\Command\ChangePassword;
use App\Security\PasswordEncoder;
use App\Security\Security;
use App\Util\Assert;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Xm\SymfonyBundle\Util\StringUtil;

class UserPasswordMutation implements MutationInterface
{
    /** @var MessageBusInterface */
    private $commandBus;

    /** @var UserPasswordEncoderInterface */
    private $userPasswordEncoder;

    /** @var PasswordEncoder */
    private $passwordEncoder;

    /** @var Security */
    private $security;

    public function __construct(
        MessageBusInterface $commandBus,
        UserPasswordEncoderInterface $userPasswordEncoder,
        PasswordEncoder $passwordEncoder,
        Security $security
    ) {
        $this->commandBus = $commandBus;
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->passwordEncoder = $passwordEncoder;
        $this->security = $security;
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
            'Current password cannot be empty.'
        );
        Assert::true(
            $this->userPasswordEncoder->isPasswordValid($user, $currentPassword),
            'Current password does not match.'
        );

        // check new password
        Assert::passwordLength($newPassword);
        Assert::passwordComplexity($newPassword, [
            $user->getUsername(),
            $user->firstName(),
            $user->lastName(),
        ]);
        Assert::compromisedPassword($newPassword);

        $encodedPassword = ($this->passwordEncoder)(
            $user->firstRole(),
            $newPassword
        );

        $this->commandBus->dispatch(
            ChangePassword::forUser(
                $user->userId(),
                $encodedPassword
            )
        );

        return [
            'success' => true,
        ];
    }
}
