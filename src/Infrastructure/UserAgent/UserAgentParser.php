<?php

declare(strict_types=1);

namespace App\Infrastructure\UserAgent;

use donatj\UserAgent\UserAgentParser as DonatjUserAgentParser;

final readonly class UserAgentParser
{
    /**
     * Returns null when there's no user agent.
     */
    public function parse(?string $userAgent): ?ParsedUserAgent
    {
        // the library parses the current request's user agent when given null
        if (null === $userAgent || '' === trim($userAgent)) {
            return null;
        }

        $parsed = new DonatjUserAgentParser()->parse($userAgent);

        return new ParsedUserAgent(
            $parsed->browser(),
            // eg "Chrome/" with no number gives an empty version
            '' === $parsed->browserVersion() ? null : $parsed->browserVersion(),
            $parsed->platform(),
        );
    }
}
