<?php

declare(strict_types=1);

namespace App\Tests\Model\Auth;

use App\Model\Auth\Auth;
use App\Model\Auth\Event\UserFailedToLogin;
use App\Model\Auth\Event\UserLoggedIn;
use App\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\FakeAr;

class AuthTest extends BaseTestCase
{
    public function testSuccess(): void
    {
        $faker = $this->faker();

        $authId = $faker->authId();
        $userId = $faker->userId();
        $email = $faker->emailVo();
        $userAgent = $faker->userAgent();
        $ipAddress = $faker->ipv4();
        $route = $faker->slug();

        $auth = Auth::success($authId, $userId, $email, $userAgent, $ipAddress, $route);

        $this->assertInstanceOf(Auth::class, $auth);

        $events = $this->popRecordedEvent($auth);

        $this->assertRecordedEvent(UserLoggedIn::class, [
            'userId'    => $userId->toString(),
            'email'     => $email->toString(),
            'userAgent' => $userAgent,
            'ipAddress' => $ipAddress,
            'route'     => $route,
        ], $events);

        $this->assertCount(1, $events);

        $this->assertEquals($authId, $auth->authId());
    }

    public function testFailure(): void
    {
        $faker = $this->faker();

        $authId = $faker->authId();
        $email = $faker->email();
        $userAgent = $faker->userAgent();
        $ipAddress = $faker->ipv4();
        $message = $faker->asciify(str_repeat('*', 100));
        $route = $faker->slug();

        $auth = Auth::failure($authId, $email, $userAgent, $ipAddress, $message, $route);

        $this->assertInstanceOf(Auth::class, $auth);

        $events = $this->popRecordedEvent($auth);

        $this->assertRecordedEvent(UserFailedToLogin::class, [
            'email'            => $email,
            'userAgent'        => $userAgent,
            'ipAddress'        => $ipAddress,
            'exceptionMessage' => $message,
            'route'            => $route,
        ], $events);

        $this->assertCount(1, $events);

        $this->assertEquals($authId, $auth->authId());
    }

    public function testFailureNullValues(): void
    {
        $faker = $this->faker();

        $authId = $faker->authId();
        $ipAddress = $faker->ipv4();
        $message = $faker->asciify(str_repeat('*', 100));
        $route = $faker->slug();

        $auth = Auth::failure($authId, null, null, $ipAddress, $message, $route);

        $this->assertInstanceOf(Auth::class, $auth);

        $events = $this->popRecordedEvent($auth);

        $this->assertRecordedEvent(UserFailedToLogin::class, [
            'email'            => null,
            'userAgent'        => null,
            'ipAddress'        => $ipAddress,
            'exceptionMessage' => $message,
            'route'            => $route,
        ], $events);

        $this->assertCount(1, $events);
    }

    public function testSameIdentityAs(): void
    {
        $faker = $this->faker();

        $authId = $faker->authId();
        $userId = $faker->userId();
        $email = $faker->emailVo();
        $userAgent = $faker->userAgent();
        $ipAddress = $faker->ipv4();
        $route = $faker->slug();

        $auth1 = Auth::success($authId, $userId, $email, $userAgent, $ipAddress, $route);
        $auth2 = Auth::success($authId, $userId, $email, $userAgent, $ipAddress, $route);

        $this->assertTrue($auth1->sameIdentityAs($auth2));
    }

    public function testSameIdentityAsFalse(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();
        $email = $faker->emailVo();
        $userAgent = $faker->userAgent();
        $ipAddress = $faker->ipv4();
        $route = $faker->slug();

        $auth1 = Auth::success($faker->authId(), $userId, $email, $userAgent, $ipAddress, $route);
        $auth2 = Auth::success($faker->authId(), $userId, $email, $userAgent, $ipAddress, $route);

        $this->assertFalse($auth1->sameIdentityAs($auth2));
    }

    public function testSameIdentityAsDiffClass(): void
    {
        $faker = $this->faker();

        $auth = Auth::success(
            $faker->authId(),
            $faker->userId(),
            $faker->emailVo(),
            $faker->userAgent(),
            $faker->ipv4(),
            $faker->slug(),
        );

        $this->assertFalse($auth->sameIdentityAs(FakeAr::create()));
    }
}
