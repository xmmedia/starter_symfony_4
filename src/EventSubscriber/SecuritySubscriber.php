<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Model\Auth\AuthId;
use App\Model\Auth\Command\UserFailedToLogin;
use App\Model\Auth\Command\UserLoggedInSuccessfully;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

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
        $authId = AuthId::generate();
        $user = $event->getAuthenticationToken()->getUser();
        $request = $event->getRequest();

        // @todo update login count on user
        $this->commandBus->dispatch(
            UserLoggedInSuccessfully::now(
                $authId,
                $user->id(),
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
        $authId = AuthId::generate();
        $token = $event->getAuthenticationToken();
        $request = $this->requestStack->getCurrentRequest();

        $this->commandBus->dispatch(
            UserFailedToLogin::now(
                $authId,
                $token->getCredentials()->email(),
                $request->headers->get('User-Agent'),
                $request->getClientIp(),
                $event->getAuthenticationException()->getMessage()
            )
        );
    }
}
