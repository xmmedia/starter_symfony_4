<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Entity\User;
use App\Security\Exception\AccountInactiveException;
use App\Security\Exception\AccountNotVerifiedException;
use App\Security\UserChecker;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

class UserCheckerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testCheckPreAuth(): void
    {
        $checker = new UserChecker();

        $user = Mockery::mock(User::class);
        $user->shouldNotReceive('verified');
        $user->shouldNotReceive('active');

        $checker->checkPreAuth($user);
    }

    public function testCheckPreAuthDiffUser(): void
    {
        $checker = new UserChecker();

        $user = Mockery::mock(UserInterface::class);
        $user->shouldNotReceive('verified');
        $user->shouldNotReceive('active');

        $checker->checkPreAuth($user);
    }

    public function testCheckPostAuthValid(): void
    {
        $checker = new UserChecker();

        $user = Mockery::mock(User::class);
        $user->shouldReceive('verified')
            ->once()
            ->andReturnTrue();
        $user->shouldReceive('active')
            ->once()
            ->andReturnTrue();

        $checker->checkPostAuth($user);
    }

    public function testCheckPostAuthNotVerified(): void
    {
        $checker = new UserChecker();

        $user = Mockery::mock(User::class);
        $user->shouldReceive('verified')
            ->once()
            ->andReturnfalse();
        $user->shouldNotReceive('active');

        $this->expectException(AccountNotVerifiedException::class);

        $checker->checkPostAuth($user);
    }

    public function testCheckPostAuthNotActive(): void
    {
        $checker = new UserChecker();

        $user = Mockery::mock(User::class);
        $user->shouldReceive('verified')
            ->once()
            ->andReturnTrue();
        $user->shouldReceive('active')
            ->once()
            ->andReturnFalse();

        $this->expectException(AccountInactiveException::class);

        $checker->checkPostAuth($user);
    }

    public function testCheckPostAuthDiffUser(): void
    {
        $checker = new UserChecker();

        $user = Mockery::mock(UserInterface::class);
        $user->shouldNotReceive('verified');
        $user->shouldNotReceive('active');

        $checker->checkPostAuth($user);
    }
}
