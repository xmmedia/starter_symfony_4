<?php

declare(strict_types=1);

namespace App\Tests\EventStore;

use App\DataProvider\IssuerProvider;
use App\EventSourcing\AggregateChanged;
use App\EventStore\MetadataIssuedByEnricher;
use Faker;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class MetadataIssuedByEnricherTest extends TestCase
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

        $enricher = new MetadataIssuedByEnricher($issuerProvider);

        $event = $enricher->enrich(AggregateChanged::occur($faker->uuid, []));

        $this->assertArrayHasKey('issuedBy', $event->metadata());
        $this->assertArraySubset(['issuedBy' => $uuid], $event->metadata());
    }
}
