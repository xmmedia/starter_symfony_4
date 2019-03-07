<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\GraphQl\Type;

use App\Infrastructure\GraphQl\Type\CountryInputType;
use App\Model\Country;
use GraphQL\Error\Error;
use PHPUnit\Framework\TestCase;

class CountryInputTypeTest extends TestCase
{
    public function test(): void
    {
        $type = new CountryInputType();

        $this->assertCount(2, $type->getValues());
    }

    public function testSerialize(): void
    {
        $type = new CountryInputType();

        $result = $type->serialize(Country::fromString('CA'));

        $this->assertEquals('CA', $result);
    }

    public function testSerializeNotCountry(): void
    {
        $type = new CountryInputType();

        $this->expectException(Error::class);

        $type->serialize('string');
    }

    public function testGetAliases(): void
    {
        $this->assertEquals(['CountryInput'], CountryInputType::getAliases());
    }
}
