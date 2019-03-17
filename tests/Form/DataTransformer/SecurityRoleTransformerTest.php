<?php

declare(strict_types=1);

namespace App\Tests\Form\DataTransformer;

use App\DataProvider\RoleProvider;
use App\Form\DataTransformer\SecurityRoleTransformer;
use App\Tests\BaseTestCase;
use Mockery;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Security\Core\Role\Role;

class SecurityRoleTransformerTest extends BaseTestCase
{
    /**
     * @dataProvider transformProvider
     */
    public function testTransform(?Role $value, ?string $expected): void
    {
        $roleProvider = Mockery::mock(RoleProvider::class);

        $result = (new SecurityRoleTransformer($roleProvider))->transform($value);

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
    public function testReverseTransform(?string $value, ?Role $expected): void
    {
        $roleProvider = Mockery::mock(RoleProvider::class);
        if (null !== $value) {
            $roleProvider->shouldReceive('__invoke')
                ->andReturn(['ROLE_USER']);
        }

        $result = (new SecurityRoleTransformer($roleProvider))
            ->reverseTransform($value);

        $this->assertEquals($expected, $result);
    }

    public function reverseTransformProvider(): \Generator
    {
        yield [null, null];

        yield ['ROLE_USER', new Role('ROLE_USER')];
    }

    public function testReverseTransformInvalid(): void
    {
        $roleProvider = Mockery::mock(RoleProvider::class);
        $roleProvider->shouldReceive('__invoke')
            ->andReturn(['ROLE_USER']);

        $this->expectException(TransformationFailedException::class);

        (new SecurityRoleTransformer($roleProvider))->reverseTransform('ROLE');
    }
}
