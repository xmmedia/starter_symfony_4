<?php

declare(strict_types=1);

namespace App\Tests\Security\Voter;

use App\Security\Security;
use Mockery;

trait VoterSecurityTrait
{
    /**
     * @param array $methods Key is method name and value is return value. Each method needs to be called once.
     */
    private function getSecurityMock(array $methods = []): Security
    {
        $security = Mockery::mock(Security::class);

        foreach ($methods as $method => $return) {
            $security->shouldReceive($method)
                ->once()
                ->andReturn($return);
        }

        return $security;
    }
}
