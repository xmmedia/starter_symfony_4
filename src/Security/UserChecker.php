<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Model\User\Command\ActivateUser;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Xm\SymfonyBundle\Infrastructure\Service\RequestInfoProvider;
use Xm\SymfonyBundle\Security\Exception\AccountInactiveException;
use Xm\SymfonyBundle\Security\Exception\AccountNotVerifiedException;

readonly class UserChecker implements UserCheckerInterface
{
    public function __construct(
        private RequestInfoProvider $requestInfoProvider,
        private HttpUtils $httpUtils,
        private MessageBusInterface $commandBus,
        private ManagerRegistry $doctrine,
    ) {
    }

    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$this->httpUtils->checkRequestPath($this->requestInfoProvider->currentRequest(), 'app_login_link')) {
            return;
        }

        // successfully logged in via magic link, but their account has not been verified
        if (!$user->verified()) {
            $this->commandBus->dispatch(
                ActivateUser::now($user->userId()),
            );

            $this->doctrine->getManagerForClass(User::class)->refresh($user);
        }
    }

    /**
     * Exceptions/messages generated here can be displayed to the user
     * because they've entered the correct password.
     */
    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->verified()) {
            $ex = new AccountNotVerifiedException('User account has not been verified.');
            $ex->setUser($user);
            throw $ex;
        }

        if (!$user->active()) {
            $ex = new AccountInactiveException('User account is not active.');
            $ex->setUser($user);
            throw $ex;
        }
    }
}
