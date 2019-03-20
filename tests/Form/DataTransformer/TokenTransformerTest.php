<?php

declare(strict_types=1);

namespace App\Tests\Form\DataTransformer;

use App\Form\DataTransformer\TokenTransformer;
use App\Model\User\Token;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\TransformationFailedException;

class TokenTransformerTest extends TestCase
{
    /**
     * @dataProvider transformProvider
     */
    public function testTransform($value, $expected): void
    {
        $result = (new TokenTransformer())->transform($value);

        $this->assertEquals($expected, $result);
    }

    public function transformProvider(): \Generator
    {
        yield [null, null];

        yield [Token::fromString('string'), 'string'];
    }

    /**
     * @dataProvider reverseTransformProvider
     */
    public function testReverseTransform($value, $expected): void
    {
        $result = (new TokenTransformer())->reverseTransform($value);

        $this->assertEquals($expected, $result);
    }

    public function reverseTransformProvider(): \Generator
    {
        yield [null, null];

        yield ['string', Token::fromString('string')];
    }

    public function testReverseTransformInvalid(): void
    {
        $this->expectException(TransformationFailedException::class);

        (new TokenTransformer())->reverseTransform('');
    }
}
