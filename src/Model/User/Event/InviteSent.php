<?php

declare(strict_types=1);

namespace App\Model\User\Event;

use App\Model\User\Token;
use App\Model\User\UserId;
use Xm\SymfonyBundle\EventSourcing\AggregateChanged;
use Xm\SymfonyBundle\Model\EmailGatewayMessageId;
use Xm\SymfonyBundle\Model\NotificationGatewayId;

class InviteSent extends AggregateChanged
{
    /** @var Token */
    private $token;

    /** @var NotificationGatewayId */
    private $messageId;

    public static function now(
        UserId $userId,
        Token $token,
        NotificationGatewayId $messageId
    ): self {
        $event = self::occur($userId->toString(), [
            'token'     => $token->toString(),
            'messageId' => $messageId->toString(),
        ]);

        $event->token = $token;
        $event->messageId = $messageId;

        return $event;
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->aggregateId());
    }

    public function token(): Token
    {
        if (null === $this->token) {
            $this->token = Token::fromString($this->payload['token']);
        }

        return $this->token;
    }

    public function messageId(): NotificationGatewayId
    {
        if (null === $this->messageId) {
            $this->messageId = EmailGatewayMessageId::fromString(
                $this->payload['messageId']
            );
        }

        return $this->messageId;
    }
}
