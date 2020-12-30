<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\EventSubscriber\GraphQlErrorSubscriber;
use App\Security\Security;
use App\Tests\BaseTestCase;
use GraphQL\Error\Error;
use Mockery;
use Overblog\GraphQLBundle\Event\ErrorFormattingEvent;
use Overblog\GraphQLBundle\Event\Events;

class GraphQlErrorSubscriberTest extends BaseTestCase
{
    public function testSubscribedEvents(): void
    {
        $subscribed = GraphQlErrorSubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(Events::ERROR_FORMATTING, $subscribed);

        $this->assertEquals(
            -128,
            $subscribed[key($subscribed)][1]
        );
    }

    public function testCannotQueryErrorMessage(): void
    {
        $faker = $this->faker();

        $subscribed = GraphQlErrorSubscriber::getSubscribedEvents();
        $method = $subscribed[Events::ERROR_FORMATTING][0];

        $security = Mockery::mock(Security::class);
        $security->shouldReceive('isLoggedIn')
            ->once()
            ->andReturnFalse();

        $error = new Error('Cannot query field '.$faker->sentence);

        $event = new ErrorFormattingEvent($error, []);

        (new GraphQlErrorSubscriber($security))->{$method}($event);

        $this->assertTrue($event->getFormattedError()->offsetExists('message'));
        $this->assertEquals(
            'Access denied to this field.',
            $event->getFormattedError()->offsetGet('message')
        );
        $this->assertTrue($event->getFormattedError()->offsetExists('code'));
        $this->assertEquals(
            401,
            $event->getFormattedError()->offsetGet('code')
        );
    }

    public function testAccessDeniedErrorMessage(): void
    {
        $subscribed = GraphQlErrorSubscriber::getSubscribedEvents();
        $method = $subscribed[Events::ERROR_FORMATTING][0];

        $security = Mockery::mock(Security::class);
        $security->shouldReceive('isLoggedIn')
            ->once()
            ->andReturnFalse();

        $error = new Error('Access denied to this field.');

        $event = new ErrorFormattingEvent($error, []);

        (new GraphQlErrorSubscriber($security))->{$method}($event);

        $this->assertTrue($event->getFormattedError()->offsetExists('code'));
        $this->assertEquals(
            401,
            $event->getFormattedError()->offsetGet('code')
        );
    }

    public function testWithExceptionCode(): void
    {
        $faker = $this->faker();
        $code = $faker->numberBetween(1, 250);

        $subscribed = GraphQlErrorSubscriber::getSubscribedEvents();
        $method = $subscribed[Events::ERROR_FORMATTING][0];

        $security = Mockery::mock(Security::class);
        $security->shouldReceive('isLoggedIn')
            ->once()
            ->andReturnTrue();

        $exception = new \Exception('', $code);

        $error = new Error(
            $faker->string(5),
            null,
            null,
            null,
            null,
            $exception,
        );

        $event = new ErrorFormattingEvent($error, []);

        (new GraphQlErrorSubscriber($security))->{$method}($event);

        $this->assertTrue($event->getFormattedError()->offsetExists('code'));
        $this->assertEquals(
            $code,
            $event->getFormattedError()->offsetGet('code')
        );
    }

    public function testWithExceptionCodeOverrides401(): void
    {
        $faker = $this->faker();
        $code = $faker->numberBetween(1, 250);

        $subscribed = GraphQlErrorSubscriber::getSubscribedEvents();
        $method = $subscribed[Events::ERROR_FORMATTING][0];

        $security = Mockery::mock(Security::class);
        $security->shouldReceive('isLoggedIn')
            ->once()
            ->andReturnTrue();

        $error = new Error(
            'Access denied to this field.',
            null,
            null,
            null,
            null,
            new \Exception('', $code),
        );

        $event = new ErrorFormattingEvent($error, []);

        (new GraphQlErrorSubscriber($security))->{$method}($event);

        $this->assertTrue($event->getFormattedError()->offsetExists('code'));
        $this->assertEquals(
            $code,
            $event->getFormattedError()->offsetGet('code')
        );
    }

    public function testNoExceptionCode(): void
    {
        $faker = $this->faker();

        $subscribed = GraphQlErrorSubscriber::getSubscribedEvents();
        $method = $subscribed[Events::ERROR_FORMATTING][0];

        $security = Mockery::mock(Security::class);
        $security->shouldReceive('isLoggedIn')
            ->once()
            ->andReturnTrue();

        $error = new Error(
            $faker->string(5),
            null,
            null,
            null,
            null,
            new \Exception('', 0),
        );

        $event = new ErrorFormattingEvent($error, []);

        (new GraphQlErrorSubscriber($security))->{$method}($event);

        $this->assertFalse($event->getFormattedError()->offsetExists('code'));
    }

    public function testNoPreviousException(): void
    {
        $faker = $this->faker();

        $subscribed = GraphQlErrorSubscriber::getSubscribedEvents();
        $method = $subscribed[Events::ERROR_FORMATTING][0];

        $security = Mockery::mock(Security::class);
        $security->shouldReceive('isLoggedIn')
            ->once()
            ->andReturnTrue();

        $error = new Error($faker->string(5));

        $event = new ErrorFormattingEvent($error, []);

        (new GraphQlErrorSubscriber($security))->{$method}($event);

        $this->assertFalse($event->getFormattedError()->offsetExists('code'));
    }
}
