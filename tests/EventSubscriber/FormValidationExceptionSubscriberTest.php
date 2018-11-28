<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\EventSubscriber\FormValidationExceptionSubscriber;
use App\Exception\FormValidationException;
use Exception;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Serializer\SerializerInterface;

class FormValidationExceptionSubscriberTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testSubscribedEvents(): void
    {
        $subscribed = FormValidationExceptionSubscriber::getSubscribedEvents();

        $this->assertEquals(
            -100,
            $subscribed[key($subscribed)][1]
        );
    }

    public function testOnKernelExceptionInvalidForm(): void
    {
        $subscribed = FormValidationExceptionSubscriber::getSubscribedEvents();
        $method = $subscribed[key($subscribed)][0];

        $form = Mockery::mock(FormInterface::class);
        $exception = FormValidationException::fromForm($form);

        $serializer = Mockery::mock(SerializerInterface::class);
        $serializer->shouldReceive('serialize')
            ->once()
            ->with($form, 'json', ['json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS])
            ->andReturn('{}');

        $subscriber = new FormValidationExceptionSubscriber($serializer);

        $event = Mockery::mock(GetResponseForExceptionEvent::class);

        $event->shouldReceive('getException')
            ->once()
            ->andReturn($exception);

        $event->shouldReceive('setResponse')
            ->once()
            ->with(JsonResponse::class);

        $subscriber->{$method}($event);
    }

    public function testOnKernelExceptionOtherException(): void
    {
        $subscribed = FormValidationExceptionSubscriber::getSubscribedEvents();
        $method = $subscribed[key($subscribed)][0];

        $serializer = Mockery::mock(SerializerInterface::class);

        $subscriber = new FormValidationExceptionSubscriber($serializer);

        $event = Mockery::mock(GetResponseForExceptionEvent::class);

        $exception = new Exception();

        $event->shouldReceive('getException')
            ->once()
            ->andReturn($exception);

        $subscriber->{$method}($event);
    }
}
