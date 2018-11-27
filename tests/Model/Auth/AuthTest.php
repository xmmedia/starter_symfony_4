<?php

declare(strict_types=1);

namespace App\Tests\Model\Auth;

use App\Model\Auth\Auth;
use App\Model\Auth\AuthId;
use App\Model\Auth\Event\UserFailedToLogin;
use App\Model\Auth\Event\UserLoggedIn;
use App\Model\Email;
use App\Model\User\UserId;
use App\Tests\BaseTestCase;
use Faker;

class AuthTest extends BaseTestCase
{
    public function testSuccess(): void
    {
        $faker = Faker\Factory::create();

        $authId = AuthId::generate();
        $userId = UserId::generate();
        $email = Email::fromString($faker->email);
        $userAgent = $faker->userAgent;
        $ipAddress = $faker->ipv4;

        $auth = Auth::success($authId, $userId, $email, $userAgent, $ipAddress);

        $this->assertInstanceOf(Auth::class, $auth);

        $events = $this->popRecordedEvent($auth);

        $this->assertRecordedEvent(UserLoggedIn::class, [
            'userId'    => $userId->toString(),
            'email'     => $email->toString(),
            'userAgent' => $userAgent,
            'ipAddress' => $ipAddress,
        ], $events);

        $this->assertCount(1, $events);

        $this->assertEquals($authId, $auth->authId());
    }

    public function testFailure(): void
    {
        $faker = Faker\Factory::create();

        $authId = AuthId::generate();
        $email = $faker->email;
        $userAgent = $faker->userAgent;
        $ipAddress = $faker->ipv4;
        $message = $faker->asciify(str_repeat('*', 100));

        $auth = Auth::failure($authId, $email, $userAgent, $ipAddress, $message);

        $this->assertInstanceOf(Auth::class, $auth);

        $events = $this->popRecordedEvent($auth);

        $this->assertRecordedEvent(UserFailedToLogin::class, [
            'email'            => $email,
            'userAgent'        => $userAgent,
            'ipAddress'        => $ipAddress,
            'exceptionMessage' => $message,
        ], $events);

        $this->assertCount(1, $events);

        $this->assertEquals($authId, $auth->authId());
    }

    public function testSameIdentityAs(): void
    {
        $faker = Faker\Factory::create();

        $authId = AuthId::generate();
        $userId = UserId::generate();
        $email = Email::fromString($faker->email);
        $userAgent = $faker->userAgent;
        $ipAddress = $faker->ipv4;

        $auth1 = Auth::success($authId, $userId, $email, $userAgent, $ipAddress);
        $auth2 = Auth::success($authId, $userId, $email, $userAgent, $ipAddress);

        $this->assertTrue($auth1->sameIdentityAs($auth2));
    }
}
