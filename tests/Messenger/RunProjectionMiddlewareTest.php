<?php

declare(strict_types=1);

namespace App\Tests\Messenger;

use App\Messenger\RunProjectionMiddleware;
use App\Model\Auth\Event\UserLoggedIn;
use App\Model\User\Event\UserActivatedByAdmin;
use App\Model\User\Event\UserWasAddedByAdmin;
use App\Model\User\Name;
use App\Model\User\Role;
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

        new RunProjectionMiddleware($projectionRunner)->handle(
            new Envelope($message),
            $this->getStackMock(),
        );
    }

    public static function messageDataProvider(): \Generator
    {
        $faker = self::makeFaker();

        yield 'UserActivatedByAdmin event' => [
            UserActivatedByAdmin::now($faker->userId()),
            ['user_projection'],
        ];

        yield 'UserWasAddedByAdmin event' => [
            UserWasAddedByAdmin::now(
                $faker->userId(),
                $faker->emailVo(),
                $faker->password(),
                Role::ROLE_USER(),
                true,
                Name::fromString($faker->firstName()),
                Name::fromString($faker->lastName()),
                false,
                $faker->userData(),
            ),
            ['user_projection'],
        ];

        yield 'UserLoggedIn event' => [
            UserLoggedIn::now(
                $faker->authId(),
                $faker->userId(),
                $faker->emailVo(),
                $faker->userAgent(),
                $faker->ipv4(),
                'app_login',
            ),
            ['auth_projection'],
        ];
    }

    public function testNotAggregateChangedMessage(): void
    {
        $projectionRunner = \Mockery::mock(ProjectionRunner::class);
        $projectionRunner->shouldNotReceive('run');

        new RunProjectionMiddleware($projectionRunner)->handle(
            new Envelope(new \stdClass()),
            $this->getStackMock(),
        );
    }

    public function testMessageInRootNamespace(): void
    {
        $projectionRunner = \Mockery::mock(ProjectionRunner::class);
        $projectionRunner->shouldNotReceive('run');

        $message = \Mockery::mock(AggregateChanged::class);

        new RunProjectionMiddleware($projectionRunner)->handle(
            new Envelope($message),
            $this->getStackMock(),
        );
    }

    public function testNotAggregateChanged(): void
    {
        $projectionRunner = \Mockery::mock(ProjectionRunner::class);
        $projectionRunner->shouldNotReceive('run');

        $message = \Mockery::mock(UserLoggedIn::class);

        new RunProjectionMiddleware($projectionRunner)->handle(
            new Envelope($message),
            $this->getStackMock(),
        );
    }

    public function testPausePreventsProjectionFromRunning(): void
    {
        $faker = $this->faker();
        $event = UserLoggedIn::now(
            $faker->authId(),
            $faker->userId(),
            $faker->emailVo(),
            $faker->userAgent(),
            $faker->ipv4(),
            'app_login',
        );

        $projectionRunner = \Mockery::mock(ProjectionRunner::class);
        $projectionRunner->shouldNotReceive('run');

        $middleware = new RunProjectionMiddleware($projectionRunner);
        $middleware->pause();

        $middleware->handle(
            new Envelope($event),
            $this->getStackMock(),
        );
    }

    public function testResumeAllowsProjectionToRunAgain(): void
    {
        $faker = $this->faker();
        $event = UserLoggedIn::now(
            $faker->authId(),
            $faker->userId(),
            $faker->emailVo(),
            $faker->userAgent(),
            $faker->ipv4(),
            'app_login',
        );

        $projectionRunner = \Mockery::mock(ProjectionRunner::class);
        $projectionRunner->shouldReceive('run')
            ->once()
            ->with('auth_projection');

        $middleware = new RunProjectionMiddleware($projectionRunner);
        $middleware->pause();
        $middleware->resume();

        $middleware->handle(
            new Envelope($event),
            $this->getStackMock(),
        );
    }

    public function testMultiplePauseResumeToggles(): void
    {
        $faker = $this->faker();
        $event = UserWasAddedByAdmin::now(
            $faker->userId(),
            $faker->emailVo(),
            $faker->password(),
            Role::ROLE_USER(),
            true,
            Name::fromString($faker->firstName()),
            Name::fromString($faker->lastName()),
            false,
            $faker->userData(),
        );

        $projectionRunner = \Mockery::mock(ProjectionRunner::class);
        $projectionRunner->shouldNotReceive('run');

        $middleware = new RunProjectionMiddleware($projectionRunner);

        // Pause, resume, pause again - should end paused
        $middleware->pause();
        $middleware->resume();
        $middleware->pause();

        $middleware->handle(
            new Envelope($event),
            $this->getStackMock(),
        );
    }
}
