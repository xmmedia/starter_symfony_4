<?php

declare(strict_types=1);

namespace App\Tests\Projection\User;

use App\Entity\User;
use App\Projection\User\UserTokenFinder;
use App\Tests\BaseTestCase;
use Carbon\CarbonImmutable;
use Doctrine\Persistence\ManagerRegistry;

class UserTokenFinderTest extends BaseTestCase
{
    public function testCreateResetPasswordRequest(): void
    {
        $expiresAt = CarbonImmutable::now()->addHour();
        $selector = bin2hex(random_bytes(20));
        $hashedToken = bin2hex(random_bytes(20));

        $user = \Mockery::mock(User::class);

        $finder = new UserTokenFinder(\Mockery::mock(ManagerRegistry::class));

        $result = $finder->createResetPasswordRequest($user, $expiresAt, $selector, $hashedToken);

        $this->assertSame($user, $result->getUser());
        $this->assertNotNull($result->getId());
    }
}
