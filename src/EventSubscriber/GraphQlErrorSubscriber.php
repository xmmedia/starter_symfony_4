<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Exception\FormValidationException;
use Overblog\GraphQLBundle\Event\ErrorFormattingEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GraphQlErrorSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'graphql.error_formatting' => ['onGraphqlError', -128],
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
