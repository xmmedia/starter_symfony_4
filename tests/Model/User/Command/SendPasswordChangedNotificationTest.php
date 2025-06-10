<?php

declare(strict_types=1);

namespace App\Tests\Model\User\Command;

use App\Model\User\Command\SendPasswordChangedNotification;
use App\Tests\BaseTestCase;

class SendPasswordChangedNotificationTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId();

        $command = SendPasswordChangedNotification::now($userId);

        $this->assertTrue($userId->sameValueAs($command->userId()));
    }
}
