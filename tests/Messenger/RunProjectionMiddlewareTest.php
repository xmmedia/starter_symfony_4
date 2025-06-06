<?php

declare(strict_types=1);

namespace App\Tests\Messenger;

use App\Messenger\RunProjectionMiddleware;
use App\Model\Auth\Event\UserLoggedIn;
use App\Model\User\Event\UserActivatedByAdmin;
use App\Tests\BaseTestCase;
use Symfony\Component\Messenger\Envelope;
use Xm\SymfonyBundle\EventSourcing\AggregateChanged;
use Xm\SymfonyBundle\Infrastructure\Service\ProjectionRunner;
use Xm\SymfonyBundle\Tests\MessengerMiddlewareTestTrait;

class RunProjectionMiddlewareTest extends BaseTestCase
{
    use MessengerMiddlewareTestTrait;

    #[\PHPUnit\Framework\Attributes\DataProvider('messageDataProvider')]
    public function test(AggregateChanged $message, array $projectionNames): void
    {
        $projectionRunner = \Mockery::mock(ProjectionRunner::class);
        foreach ($projectionNames as $projectionName) {
            $projectionRunner->shouldReceive('run')
                ->once()
                ->with($projectionName);
        }

        (new RunProjectionMiddleware($projectionRunner))->handle(
            new Envelope($message),
            $this->getStackMock(),
        );
    }

    public function messageDataProvider(): \Generator
    {
        $faker = $this->faker();

        yield [
            UserActivatedByAdmin::now($faker->userId()),
            ['user_projection'],
        ];
    }

    public function testNotAggregateChangedMessage(): void
    {
        $projectionRunner = \Mockery::mock(ProjectionRunner::class);
        $projectionRunner->shouldNotReceive('run');

        (new RunProjectionMiddleware($projectionRunner))->handle(
            new Envelope(new \stdClass()),
            $this->getStackMock(),
        );
    }

    public function testMessageInRootNamespace(): void
    {
        $projectionRunner = \Mockery::mock(ProjectionRunner::class);
        $projectionRunner->shouldNotReceive('run');

        $message = \Mockery::mock(AggregateChanged::class);

        (new RunProjectionMiddleware($projectionRunner))->handle(
            new Envelope($message),
            $this->getStackMock(),
        );
    }

    public function testNotAggregateChanged(): void
    {
        $projectionRunner = \Mockery::mock(ProjectionRunner::class);
        $projectionRunner->shouldNotReceive('run');

        $message = \Mockery::mock(UserLoggedIn::class);

        (new RunProjectionMiddleware($projectionRunner))->handle(
            new Envelope($message),
            $this->getStackMock(),
        );
    }
}
