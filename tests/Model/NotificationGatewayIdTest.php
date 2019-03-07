<?php

declare(strict_types=1);

namespace App\Tests\Model;

use App\Model\NotificationGatewayId;
use App\Tests\BaseTestCase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class NotificationGatewayIdTest extends BaseTestCase
{
    use MockeryPHPUnitIntegration;

    public function testFromString(): void
    {
        $id = NotificationGatewayId::fromString('string');

        $this->assertEquals('string', $id->toString());
        $this->assertEquals('string', (string) $id);
    }

    public function testEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        NotificationGatewayId::fromString('');
    }

    public function testSameValueAs(): void
    {
        $id1 = NotificationGatewayId::fromString('string');
        $id2 = NotificationGatewayId::fromString('string');

        $this->assertTrue($id1->sameValueAs($id2));
    }

    public function testSameValueAsDiffObject(): void
    {
        $id = NotificationGatewayId::fromString('string');
        $email = \App\Model\Email::fromString('info@example.com');

        $this->assertFalse($id->sameValueAs($email));
    }
}
