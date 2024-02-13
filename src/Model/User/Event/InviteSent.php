<?php

declare(strict_types=1);

namespace App\Model\User\Event;

use App\Model\User\UserId;
use Xm\SymfonyBundle\EventSourcing\AggregateChanged;
use Xm\SymfonyBundle\Model\EmailGatewayMessageId;
use Xm\SymfonyBundle\Model\NotificationGatewayId;

final class InviteSent extends AggregateChanged
{
    private NotificationGatewayId $messageId;

    public static function now(
        UserId $userId,
        NotificationGatewayId $messageId,
    ): self {
        $event = self::occur($userId->toString(), [
            'messageId' => $messageId->toString(),
        ]);

        $event->messageId = $messageId;

        return $event;
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->aggregateId());
    }

    public function messageId(): NotificationGatewayId
    {
        if (!isset($this->messageId)) {
            $this->messageId = EmailGatewayMessageId::fromString(
                $this->payload['messageId'],
            );
        }

        return $this->messageId;
    }
}
