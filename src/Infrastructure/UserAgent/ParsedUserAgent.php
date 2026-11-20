<?php

declare(strict_types=1);

namespace App\Infrastructure\UserAgent;

final readonly class ParsedUserAgent
{
    public function __construct(
        private ?string $browser,
        private ?string $browserVersion,
        private ?string $platform,
    ) {
    }

    public function browser(): ?string
    {
        return $this->browser;
    }

    public function browserVersion(): ?string
    {
        return $this->browserVersion;
    }

    public function platform(): ?string
    {
        return $this->platform;
    }

    /**
     * eg "Chrome 142 / Macintosh", with only the browser's major version.
     */
    public function summary(): string
    {
        $browser = $this->browser;
        if (null !== $browser && null !== $this->browserVersion) {
            $browser .= ' '.explode('.', $this->browserVersion)[0];
        }

        return implode(' / ', array_filter([$browser, $this->platform])) ?: 'Unrecognised';
    }
}
