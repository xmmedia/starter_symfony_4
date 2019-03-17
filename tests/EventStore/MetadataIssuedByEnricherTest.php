<?php

declare(strict_types=1);

namespace App\Tests\EventStore;

use App\DataProvider\IssuerProvider;
use App\EventSourcing\AggregateChanged;
use App\EventStore\MetadataIssuedByEnricher;
use App\Tests\BaseTestCase;
use Mockery;

class MetadataIssuedByEnricherTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();
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
