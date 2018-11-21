<?php

declare(strict_types=1);

namespace App\Tests\Model;

use App\Model\Enum;
use PHPUnit\Framework\TestCase;

class EnumTest extends TestCase
{
    public function testSameAs(): void
    {
        /** @var Enum $enum1 */
        $enum1 = $this->getMockForAbstractClass(Enum::class, [], '', false);
        $enum2 = $this->getMockForAbstractClass(Enum::class, [], '', false);

        $this->assertTrue($enum1->sameValueAs($enum2));
    }
}
