<?php

declare(strict_types=1);

namespace App\Tests\Model\Auth\Handler;

use App\Model\Auth\Auth;
use App\Model\Auth\AuthList;
use App\Model\Auth\Command\UserLoggedInSuccessfully;
use App\Model\Auth\Handler\UserLoggedInSuccessfullyHandler;
use App\Model\Email;
use App\Tests\BaseTestCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class UserLoggedInSuccessfullyHandlerTest extends BaseTestCase
{
    use MockeryPHPUnitIntegration;

    public function test(): void
    {
        $faker = $this->faker();

        $authId = $faker->authId;
        $userId = $faker->userId;
        $email = Email::fromString($faker->email);
        $userAgent = $faker->userAgent;
        $ipAddress = $faker->ipv4;

        $command = UserLoggedInSuccessfully::now(
            $authId,
            $userId,
            $email,
            $userAgent,
            $ipAddress
        );

        $repo = Mockery::mock(AuthList::class);
        $repo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(Auth::class));

        (new UserLoggedInSuccessfullyHandler($repo))($command);
    }
}
