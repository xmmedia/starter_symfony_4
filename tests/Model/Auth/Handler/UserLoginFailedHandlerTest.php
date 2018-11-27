<?php

declare(strict_types=1);

namespace App\Tests\Model\Auth\Handler;

use App\Model\Auth\Auth;
use App\Model\Auth\AuthId;
use App\Model\Auth\AuthList;
use App\Model\Auth\Command\UserLoginFailed;
use App\Model\Auth\Handler\UserLoginFailedHandler;
use Faker;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class UserLoginFailedHandlerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test(): void
    {
        $faker = Faker\Factory::create();

        $authId = AuthId::generate();
        $email = $faker->email;
        $userAgent = $faker->userAgent;
        $ipAddress = $faker->ipv4;
        $message = $faker->asciify(str_repeat('*', 100));

        $command = UserLoginFailed::now(
            $authId,
            $email,
            $userAgent,
            $ipAddress,
            $message
        );

        $repo = Mockery::mock(AuthList::class);
        $repo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(Auth::class));

        (new UserLoginFailedHandler($repo))($command);
    }
}
