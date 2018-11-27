<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Command;

use App\Model\User\Command\UserLoggedIn;
use App\Model\User\UserId;
use PHPUnit\Framework\TestCase;

class UserLoggedInTest extends TestCase
{
    public function test(): void
    {
        $userId = UserId::generate();

        $command = UserLoggedIn::now($userId);

        $this->assertTrue($userId->sameValueAs($command->userId()));
    }
}
