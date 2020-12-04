<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\EventSubscriber\GraphQlErrorSubscriber;
use App\Tests\BaseTestCase;
use GraphQL\Error\Error;
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

    public function testWithExceptionCode(): void
    {
        $faker = $this->faker();
        $code = $faker->numberBetween(1, 250);

        $subscribed = GraphQlErrorSubscriber::getSubscribedEvents();
        $method = $subscribed[Events::ERROR_FORMATTING][0];

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

        (new GraphQlErrorSubscriber())->{$method}($event);

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

        $exception = new \Exception('', 0);

        $error = new Error(
            $faker->string(5),
            null,
            null,
            null,
            null,
            $exception,
        );

        $event = new ErrorFormattingEvent($error, []);

        (new GraphQlErrorSubscriber())->{$method}($event);

        $this->assertFalse($event->getFormattedError()->offsetExists('code'));
    }

    public function testNoPreviousException(): void
    {
        $faker = $this->faker();

        $subscribed = GraphQlErrorSubscriber::getSubscribedEvents();
        $method = $subscribed[Events::ERROR_FORMATTING][0];

        $error = new Error($faker->string(5));

        $event = new ErrorFormattingEvent($error, []);

        (new GraphQlErrorSubscriber())->{$method}($event);

        $this->assertFalse($event->getFormattedError()->offsetExists('code'));
    }
}
