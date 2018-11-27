<?php

declare(strict_types=1);

namespace App\Tests\Messenger;

use App\DataProvider\IssuerProvider;
use App\Messenger\CommandEnricherMiddleware;
use App\Model\User\Command\VerifyUser;
use App\Model\User\UserId;
use Faker;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class CommandEnricherMiddlewareTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test(): void
    {
        $faker = Faker\Factory::create();
        $uuid = $faker->uuid;

        $issuerProvider = Mockery::mock(IssuerProvider::class);
        $issuerProvider->shouldReceive('getIssuer')
            ->once()
            ->andReturn($uuid);

        $middleware = new CommandEnricherMiddleware($issuerProvider);

        $command = VerifyUser::now(UserId::generate());

        $middleware->handle($command, function ($command) use ($uuid) {
            $this->assertArrayHasKey('issuedBy', $command->metadata());
            $this->assertArraySubset(['issuedBy' => $uuid], $command->metadata());
        });
    }
}
