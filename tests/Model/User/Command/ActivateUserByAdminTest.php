<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Command;

use App\Model\User\Command\ActivateUserByAdmin;
use App\Model\User\UserId;
use PHPUnit\Framework\TestCase;

class ActivateUserByAdminTest extends TestCase
{
    public function test(): void
    {
        $userId = UserId::generate();

        $command = ActivateUserByAdmin::user($userId);

        $this->assertTrue($userId->sameValueAs($command->userId()));
    }
}
