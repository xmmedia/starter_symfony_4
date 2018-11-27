<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Command;

use App\Model\User\Command\DeactivateUserByAdmin;
use App\Model\User\UserId;
use PHPUnit\Framework\TestCase;

class DeactivateUserByAdminTest extends TestCase
{
    public function test(): void
    {
        $userId = UserId::generate();

        $command = DeactivateUserByAdmin::user($userId);

        $this->assertTrue($userId->sameValueAs($command->userId()));
    }
}
