<?php

declare(strict_types=1);

namespace App\Tests\Messenger;

use App\DataProvider\IssuerProvider;
use App\Messenger\CommandEnricherMiddleware;
use App\Model\User\Command\VerifyUser;
use App\Tests\BaseTestCase;
use Mockery;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\StackMiddleware;

class CommandEnricherMiddlewareTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();
        $uuid = $faker->uuid;

        $issuerProvider = Mockery::mock(IssuerProvider::class);
        $issuerProvider->shouldReceive('getIssuer')
            ->once()
            ->andReturn($uuid);

        $middleware = new CommandEnricherMiddleware($issuerProvider);

        $envelope = $middleware->handle(
            new Envelope(VerifyUser::now($faker->userId)),
            new StackMiddleware()
        );

        $this->assertArrayHasKey(
            'issuedBy',
            $envelope->getMessage()->metadata()
        );
        $this->assertArraySubset(
            ['issuedBy' => $uuid],
            $envelope->getMessage()->metadata()
        );
    }
}
