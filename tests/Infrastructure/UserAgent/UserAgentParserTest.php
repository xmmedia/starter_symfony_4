<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\UserAgent;

use App\Infrastructure\UserAgent\UserAgentParser;
use App\Tests\BaseTestCase;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\DataProvider;

class UserAgentParserTest extends BaseTestCase
{
    #[DataProvider('userAgentProvider')]
    public function testParse(
        string $userAgent,
        ?string $browser,
        ?string $browserVersion,
        ?string $platform,
        string $summary,
    ): void {
        $parsed = new UserAgentParser()->parse($userAgent);

        $this->assertSame($browser, $parsed->browser());
        $this->assertSame($browserVersion, $parsed->browserVersion());
        $this->assertSame($platform, $parsed->platform());
        $this->assertSame($summary, $parsed->summary());
    }

    public static function userAgentProvider(): \Generator
    {
        yield 'chrome mac' => [
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',
            'Chrome',
            '142.0.0.0',
            'Macintosh',
            'Chrome 142 / Macintosh',
        ];
        yield 'edge windows' => [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0',
            'Edge',
            '142.0.0.0',
            'Windows',
            'Edge 142 / Windows',
        ];
        yield 'safari iphone' => [
            'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.1 Mobile/15E148 Safari/604.1',
            'Safari',
            '26.1',
            'iPhone',
            'Safari 26 / iPhone',
        ];
        yield 'chrome android' => [
            'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36',
            'Chrome',
            '142.0.0.0',
            'Android',
            'Chrome 142 / Android',
        ];
        yield 'curl' => ['curl/8.7.1', 'curl', '8.7.1', null, 'curl 8'];
        yield 'no version' => [
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Chrome/',
            'Chrome',
            null,
            'Macintosh',
            'Chrome / Macintosh',
        ];
        yield 'unrecognised' => ['Mozilla/5.0', null, null, null, 'Unrecognised'];
    }

    #[DataProvider('noUserAgentProvider')]
    #[BackupGlobals(true)]
    public function testNoUserAgent(?string $userAgent): void
    {
        // the library falls back to the current request's user agent
        $_SERVER['HTTP_USER_AGENT'] = 'curl/8.7.1';

        $this->assertNull(new UserAgentParser()->parse($userAgent));
    }

    public static function noUserAgentProvider(): \Generator
    {
        yield 'null' => [null];
        yield 'empty' => [''];
        yield 'blank' => ['   '];
    }
}
