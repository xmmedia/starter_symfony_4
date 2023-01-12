<?php

declare(strict_types=1);

namespace App\Model\User\Event;

use App\Model\User\Token;
use App\Model\User\UserId;
use Xm\SymfonyBundle\EventSourcing\AggregateChanged;

class TokenGenerated extends AggregateChanged
{
    private Token $token;

    public static function now(UserId $userId, Token $token): self
    {
        $event = self::occur($userId->toString(), [
            'token' => $token->toString(),
        ]);

        $event->token = $token;

        return $event;
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->aggregateId());
    }

    public function token(): Token
    {
        if (!isset($this->token)) {
            $this->token = Token::fromString($this->payload['token']);
        }

        return $this->token;
    }
}
