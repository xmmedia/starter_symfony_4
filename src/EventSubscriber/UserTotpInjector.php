<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\User;
use App\Projection\Table;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::postLoad)]
final readonly class UserTotpInjector
{
    public function __construct(private \Doctrine\DBAL\Connection $connection)
    {
    }

    public function postLoad(PostLoadEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof User) {
            return;
        }

        try {
            $result = $this->connection->fetchAssociative(
                \sprintf('SELECT `totp_secret`, `totp_pending_secret` FROM `%s` WHERE `user_id` = ?', Table::USER_TOTP),
                [$entity->userId()->toString()],
            );
        } catch (\Throwable) {
            // Table does not exist yet (e.g. fresh install before projection has run).
            return;
        }

        if (false === $result) {
            return;
        }

        $entity->setTotpData($result['totp_secret'], $result['totp_pending_secret']);
    }
}
