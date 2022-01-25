<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Security\Security;
use Overblog\GraphQLBundle\Event\ErrorFormattingEvent;
use Overblog\GraphQLBundle\Event\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;

class GraphQlErrorSubscriber implements EventSubscriberInterface
{
    /** @var Security */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::ERROR_FORMATTING => ['onGraphqlErrorFormat', -128],
        ];
    }

    /**
     * Set the exception code in the.
     */
    public function onGraphqlErrorFormat(ErrorFormattingEvent $event): void
    {
        $message = $event->getError()->getMessage();
        $notAllowedField = 0 === strpos($message, 'Cannot query field');

        // if not logged in, always set the error to the same message
        if (!$this->security->isLoggedIn() && $notAllowedField) {
            $event->getFormattedError()->offsetSet(
                'message',
                'Access denied to this field.',
            );
        }

        // sort of a hack, but we need to be able to tell the frontend that they're logged out
        if ($notAllowedField || 'Access denied to this field.' === $message) {
            $event->getFormattedError()
                ->offsetSet('code', Response::HTTP_UNAUTHORIZED);
        }

        $exception = $event->getError()->getPrevious();
        if ($exception && $exception->getCode() > 0) {
            $event->getFormattedError()
                ->offsetSet('code', $exception->getCode());
        }
    }
}
