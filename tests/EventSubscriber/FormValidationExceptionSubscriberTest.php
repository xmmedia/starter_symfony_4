<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\EventSubscriber\FormValidationExceptionSubscriber;
use App\Tests\BaseTestCase;
use Exception;
use Mockery;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Xm\SymfonyBundle\Exception\FormValidationException;

class FormValidationExceptionSubscriberTest extends BaseTestCase
{
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

        $event = new ExceptionEvent(
            Mockery::mock(HttpKernelInterface::class),
            Request::create(''),
            HttpKernelInterface::MASTER_REQUEST,
            $exception
        );

        $subscriber->{$method}($event);

        $this->assertInstanceOf(JsonResponse::class, $event->getResponse());
    }

    public function testOnKernelExceptionOtherException(): void
    {
        $subscribed = FormValidationExceptionSubscriber::getSubscribedEvents();
        $method = $subscribed[key($subscribed)][0];

        $serializer = Mockery::mock(SerializerInterface::class);

        $subscriber = new FormValidationExceptionSubscriber($serializer);

        $event = new ExceptionEvent(
            Mockery::mock(HttpKernelInterface::class),
            Request::create(''),
            HttpKernelInterface::MASTER_REQUEST,
            new Exception()
        );

        $subscriber->{$method}($event);

        $this->assertNull($event->getResponse());
    }
}
