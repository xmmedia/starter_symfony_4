<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Command;

use App\Model\User\Command\VerifyUserByAdmin;
use App\Model\User\UserId;
use PHPUnit\Framework\TestCase;

class VerifyUserByAdminTest extends TestCase
{
    public function test(): void
    {
        $userId = UserId::generate();

        $command = VerifyUserByAdmin::now($userId);

        $this->assertTrue($userId->sameValueAs($command->userId()));
    }
}
