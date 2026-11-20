<?php

declare(strict_types=1);

namespace App\GraphQl\Query\AuthLog;

use App\Entity\AuthLog;
use App\Infrastructure\UserAgent\ParsedUserAgent;
use App\Infrastructure\UserAgent\UserAgentParser;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;

final readonly class AuthLogParsedUserAgentQuery implements QueryInterface
{
    public function __construct(private UserAgentParser $userAgentParser)
    {
    }

    public function __invoke(AuthLog $authLog): ?ParsedUserAgent
    {
        return $this->userAgentParser->parse($authLog->userAgent());
    }
}
