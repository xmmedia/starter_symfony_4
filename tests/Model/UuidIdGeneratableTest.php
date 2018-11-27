<?php

declare(strict_types=1);

namespace App\Tests\Model;

use App\Model\UuidId;
use App\Model\UuidIdGeneratable;
use App\Model\ValueObject;
use App\Tests\BaseTestCase;

class UuidIdGeneratableTest extends BaseTestCase
{
    public function testGenerate(): void
    {
        $uuid = UuidIdGeneratableId::generate();

        $this->assertInstanceOf(UuidIdGeneratableId::class, $uuid);
        $this->assertUuid($uuid->toString());
    }
}

class UuidIdGeneratableId implements ValueObject
{
    use UuidId;
    use UuidIdGeneratable;
}
