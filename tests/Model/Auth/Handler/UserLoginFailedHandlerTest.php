<?php

declare(strict_types=1);

namespace App\Tests\Model\Auth\Handler;

use App\Model\Auth\Auth;
use App\Model\Auth\AuthList;
use App\Model\Auth\Command\UserLoginFailed;
use App\Model\Auth\Handler\UserLoginFailedHandler;
use App\Tests\BaseTestCase;
use Mockery;

class UserLoginFailedHandlerTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $authId = $faker->authId;
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
