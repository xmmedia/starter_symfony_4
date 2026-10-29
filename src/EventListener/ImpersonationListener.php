<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\User;
use App\Model\Auth\AuthId;
use App\Model\Auth\Command\UserEndedImpersonating;
use App\Model\Auth\Command\UserStartedImpersonating;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Symfony\Component\Security\Http\Firewall\SwitchUserListener;

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
        $request = $event->getRequest();

        if (SwitchUserListener::EXIT_VALUE === $request->query->get('_switch_user')) {
            $this->handleExit($event);
        } else {
            $this->handleStart($event);
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

    private function handleExit(SwitchUserEvent $event): void
    {
        $request = $event->getRequest();

        /** @var User $adminUser */
        $adminUser = $event->getTargetUser();

        /** @var SwitchUserToken $currentToken */
        $currentToken = $this->tokenStorage->getToken();
        /** @var User $impersonatedUser */
        $impersonatedUser = $currentToken->getUser();

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
