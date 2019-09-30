<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Xm\SymfonyBundle\Model\Auth\AuthId;
use Xm\SymfonyBundle\Model\Auth\Command\UserLoggedInSuccessfully;
use Xm\SymfonyBundle\Model\Auth\Command\UserLoginFailed;

class SecuritySubscriber implements EventSubscriberInterface
{
    /** @var MessageBusInterface */
    private $commandBus;

    /** @var RequestStack */
    private $requestStack;

    public function __construct(
        MessageBusInterface $commandBus,
        RequestStack $requestStack
    ) {
        $this->commandBus = $commandBus;
        $this->requestStack = $requestStack;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN            => 'loginSuccess',
            AuthenticationEvents::AUTHENTICATION_FAILURE => 'loginFailure',
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
                $request->getClientIp()
            )
        );
    }

    /**
     * Logs a failed login.
     */
    public function loginFailure(AuthenticationFailureEvent $event): void
    {
        $authId = AuthId::fromUuid(Uuid::uuid4());
        $token = $event->getAuthenticationToken();
        $request = $this->requestStack->getCurrentRequest();

        $this->commandBus->dispatch(
            UserLoginFailed::now(
                $authId,
                $token->getCredentials()->email(),
                $request->headers->get('User-Agent'),
                $request->getClientIp(),
                $event->getAuthenticationException()->getMessage()
            )
        );
    }
}
