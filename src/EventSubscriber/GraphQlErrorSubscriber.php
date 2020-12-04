<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Overblog\GraphQLBundle\Event\ErrorFormattingEvent;
use Overblog\GraphQLBundle\Event\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GraphQlErrorSubscriber implements EventSubscriberInterface
{
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
        $exception = $event->getError()->getPrevious();

        if ($exception && $exception->getCode() > 0) {
            $event->getFormattedError()
                ->offsetSet('code', $exception->getCode())
            ;
        }
    }
}
