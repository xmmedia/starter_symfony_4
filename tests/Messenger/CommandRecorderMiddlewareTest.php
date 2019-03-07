<?php

declare(strict_types=1);

namespace App\Tests\Messenger;

use App\Messenger\CommandRecorderMiddleware;
use App\Model\User\Command\VerifyUser;
use App\Tests\BaseTestCase;
use Doctrine\DBAL\Connection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\StackMiddleware;

class CommandRecorderMiddlewareTest extends BaseTestCase
{
    use MockeryPHPUnitIntegration;

    public function test(): void
    {
        $faker = $this->faker();

        $connection = Mockery::mock(Connection::class);
        $connection->shouldReceive('insert')
            ->once()
            ->withArgs(function ($tableName): bool {
                return 'command_log' === $tableName;
            });

        $middleware = new CommandRecorderMiddleware($connection);

        $middleware->handle(
            new Envelope(VerifyUser::now($faker->userId)),
            new StackMiddleware()
        );
    }
}
