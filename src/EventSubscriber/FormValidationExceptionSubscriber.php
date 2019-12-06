<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Overblog\GraphQLBundle\Event\ErrorFormattingEvent;
use Overblog\GraphQLBundle\Event\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Xm\SymfonyBundle\Exception\FormValidationException;

class FormValidationExceptionSubscriber implements EventSubscriberInterface
{
    /** @var SerializerInterface|Serializer */
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION  => ['onKernelException', -100],
            Events::ERROR_FORMATTING => ['onGraphqlError', -100],
        ];
    }

    /**
     * Add a response body of the form validation errors
     * if the exception is a FormValidationException.
     *
     * @see \FOS\RestBundle\Serializer\Normalizer\FormErrorNormalizer
     */
    public function onKernelException(GetResponseForExceptionEvent $event): void
    {
        /** @var FormValidationException $exception */
        $exception = $event->getException();

        if ($exception instanceof FormValidationException) {
            $json = $this->serializer
                ->serialize($exception->getForm(), 'json', [
                    'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
                ]);

            $event->setResponse(JsonResponse::fromJsonString($json, 400));
        }
    }

    /**
     * Add the validation error details to the GraphQL formatted error
     * if the exception is a FormValidationException.
     */
    public function onGraphqlError(ErrorFormattingEvent $event): void
    {
        /** @var FormValidationException $exception */
        $exception = $event->getError()->getPrevious();
        if ($exception instanceof FormValidationException) {
            $validationErrors = $this->serializer
                ->normalize($exception->getForm(), 'array');

            if ($exception->getField()) {
                $validation = [
                    $exception->getField() => $validationErrors['errors']['children'],
                ];
            } else {
                $validation = $validationErrors['errors']['children'];
            }

            $formattedError = $event->getFormattedError();
            $formattedError->offsetSet('message', 'Validation Failed');
            $formattedError->offsetSet('category', 'validation');
            $formattedError->offsetSet('validation', $validation);
        }
    }
}
