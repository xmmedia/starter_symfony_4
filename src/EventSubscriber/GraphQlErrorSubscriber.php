<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Overblog\GraphQLBundle\Event\ErrorFormattingEvent;
use Overblog\GraphQLBundle\Event\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Xm\SymfonyBundle\Exception\FormValidationException;

class GraphQlErrorSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            Events::ERROR_FORMATTING => ['onGraphqlError', -128],
        ];
    }

    /**
     * Set the exception code in the.
     */
    public function onGraphqlError(ErrorFormattingEvent $event): void
    {
        /** @var FormValidationException $exception */
        $exception = $event->getError()->getPrevious();

        if ($exception && $exception->getCode() > 0) {
            $event->getFormattedError()
                ->offsetSet('code', $exception->getCode())
            ;
        }
    }
}
