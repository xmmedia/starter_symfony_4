<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Security\TokenGenerator;
use PHPUnit\Framework\TestCase;

class TokenGeneratorTest extends TestCase
{
    public function test(): void
    {
        $token = (new TokenGenerator())();

        $this->assertEquals(
            43,
            \strlen($token->toString()),
            'The token length is not 43.',
        );
    }
}
