<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Model\Auth\AuthId;
use App\Model\Auth\Command\UserLoggedInSuccessfully;
use App\Model\Auth\Command\UserLoginFailed;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;

class LoginLoggerSubscriber implements EventSubscriberInterface
{
    private MessageBusInterface $commandBus;

    public function __construct(MessageBusInterface $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InteractiveLoginEvent::class => 'loginSuccess',
            LoginFailureEvent::class     => 'loginFailure',
        ];
    }

    /**
     * Logs the successful login.
     */
    public function loginSuccess(InteractiveLoginEvent $event): void
    {
        $authId = AuthId::fromUuid(Uuid::uuid4());
        $user = $event->getAuthenticationToken()->getUser();
        $request = $event->getRequest();

        $this->commandBus->dispatch(
            UserLoggedInSuccessfully::now(
                $authId,
                $user->userId(),
                $user->email(),
                $request->headers->get('User-Agent'),
                $request->getClientIp(),
            )
        );
    }

    /**
     * Logs a failed login.
     */
    public function loginFailure(LoginFailureEvent $event): void
    {
        $authId = AuthId::fromUuid(Uuid::uuid4());
        $request = $event->getRequest();

        $this->commandBus->dispatch(
            UserLoginFailed::now(
                $authId,
                $request->get('email'),
                $request->headers->get('User-Agent'),
                $request->getClientIp(),
                $event->getException()->getMessage(),
            )
        );
    }
}
