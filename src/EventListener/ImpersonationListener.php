<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\User;
use App\Model\Auth\AuthId;
use App\Model\Auth\Command\UserEndedImpersonating;
use App\Model\Auth\Command\UserStartedImpersonating;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;

readonly class ImpersonationListener
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private TokenStorageInterface $tokenStorage,
    ) {
    }

    #[AsEventListener(SwitchUserEvent::class)]
    public function onSwitchUser(SwitchUserEvent $event): void
    {
        // not the _switch_user value: switching while impersonating exits first,
        // with the new user in the request but the original (non-switch) token
        if ($event->getToken() instanceof SwitchUserToken) {
            $this->handleStart($event);
        } else {
            $this->handleExit($event);
        }
    }

    private function handleStart(SwitchUserEvent $event): void
    {
        $request = $event->getRequest();

        /** @var SwitchUserToken $token */
        $token = $event->getToken();

        /** @var User $adminUser */
        $adminUser = $token->getOriginalToken()->getUser();
        /** @var User $impersonatedUser */
        $impersonatedUser = $event->getTargetUser();

        $this->commandBus->dispatch(
            UserStartedImpersonating::now(
                AuthId::fromUuid(Uuid::uuid4()),
                $adminUser->userId(),
                $impersonatedUser->userId(),
                $impersonatedUser->email(),
                $request->headers->get('User-Agent'),
                $request->getClientIp(),
                $request->attributes->get('_route'),
            ),
        );
    }

    /**
     * Signing out while impersonating ends it too.
     */
    #[AsEventListener(LogoutEvent::class)]
    public function onLogout(LogoutEvent $event): void
    {
        $token = $event->getToken();
        if (!$token instanceof SwitchUserToken) {
            return;
        }

        /** @var User $adminUser */
        $adminUser = $token->getOriginalToken()->getUser();
        /** @var User $impersonatedUser */
        $impersonatedUser = $token->getUser();

        $this->ended($event->getRequest(), $adminUser, $impersonatedUser);
    }

    private function handleExit(SwitchUserEvent $event): void
    {
        /** @var User $adminUser */
        $adminUser = $event->getTargetUser();

        /** @var SwitchUserToken $currentToken */
        $currentToken = $this->tokenStorage->getToken();
        /** @var User $impersonatedUser */
        $impersonatedUser = $currentToken->getUser();

        $this->ended($event->getRequest(), $adminUser, $impersonatedUser);
    }

    private function ended(Request $request, User $adminUser, User $impersonatedUser): void
    {
        $this->commandBus->dispatch(
            UserEndedImpersonating::now(
                AuthId::fromUuid(Uuid::uuid4()),
                $adminUser->userId(),
                $impersonatedUser->userId(),
                $request->headers->get('User-Agent'),
                $request->getClientIp(),
                $request->attributes->get('_route'),
            ),
        );
    }
}
