<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use App\Model\User\UserId;
use Carbon\CarbonImmutable;
use Doctrine\DBAL\Connection;

/**
 * Stores the user's password hash in `user_credential`.
 *
 * The hash never goes through a command or an event, so it's written here
 * directly instead of by a projection. That means it survives a projection
 * reset & isn't restored by a replay.
 */
class UserPasswordStore
{
    private const string TABLE = 'user_credential';

    public function __construct(private readonly Connection $connection)
    {
    }

    public function store(UserId $userId, #[\SensitiveParameter] string $hashedPassword): void
    {
        $this->connection->executeStatement(
            \sprintf(
                'INSERT INTO `%s` (`user_id`, `password`, `updated_at`) VALUES (?, ?, ?)'
                .' ON DUPLICATE KEY UPDATE `password` = VALUES(`password`), `updated_at` = VALUES(`updated_at`)',
                self::TABLE,
            ),
            [
                $userId->toString(),
                $hashedPassword,
                CarbonImmutable::now()->format('Y-m-d H:i:s'),
            ],
        );
    }

    public function remove(UserId $userId): void
    {
        $this->connection->delete(self::TABLE, ['user_id' => $userId->toString()]);
    }
}
