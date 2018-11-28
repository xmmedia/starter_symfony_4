<?php

declare(strict_types=1);

namespace App\Tests\Form\DataTransformer;

use App\Form\DataTransformer\SecurityRoleTransformer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Role\Role;

class SecurityRoleTransformerTest extends TestCase
{
    /**
     * @dataProvider transformProvider
     */
    public function testTransform($value, $expected): void
    {
        $result = (new SecurityRoleTransformer())->transform($value);

        $this->assertEquals($expected, $result);
    }

    public function transformProvider(): \Generator
    {
        yield [null, null];

        yield [new Role('ROLE_USER'), 'ROLE_USER'];
    }

    /**
     * @dataProvider reverseTransformProvider
     */
    public function testReverseTransform($value, $expected): void
    {
        $result = (new SecurityRoleTransformer())->reverseTransform($value);

        $this->assertEquals($expected, $result);
    }

    public function reverseTransformProvider(): \Generator
    {
        yield [null, null];

        yield ['ROLE_USER', new Role('ROLE_USER')];
    }
}
