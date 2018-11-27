<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Command;

use App\Model\User\Command\VerifyUser;
use App\Model\User\UserId;
use PHPUnit\Framework\TestCase;

class VerifyUserTest extends TestCase
{
    public function test(): void
    {
        $userId = UserId::generate();

        $command = VerifyUser::now($userId);

        $this->assertTrue($userId->sameValueAs($command->userId()));
    }
}
