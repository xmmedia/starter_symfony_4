<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Sentry;

use App\Tests\BaseTestCase;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;
use Xm\SymfonyBundle\DependencyInjection\Configuration;
use Xm\SymfonyBundle\Infrastructure\Sentry\TokenScrubber;

/**
 * The tokens this project's sentry_scrubber config (xm_symfony.yaml) replaces in what's sent to Sentry.
 */
class SentryScrubberConfigTest extends BaseTestCase
{
    public function testScrubsTheUserTokens(): void
    {
        foreach (['/activate/', '/verify/', '/recover/reset/'] as $path) {
            $this->assertSame(
                $path.'[Filtered]',
                $this->scrubber()->scrub($path.$this->faker()->regexify('[A-Za-z0-9]{40}')),
            );
        }
    }

    public function testScrubsTheLoginLinkHash(): void
    {
        $this->assertSame(
            '/login-link?expires=1&hash=[Filtered]',
            $this->scrubber()->scrub('/login-link?expires=1&hash='.$this->faker()->regexify('[A-Za-z0-9_~-]{43}')),
        );
    }

    private function scrubber(): TokenScrubber
    {
        $yaml = Yaml::parseFile(\dirname(__DIR__, 3).'/config/packages/xm_symfony.yaml');
        $config = new Processor()->processConfiguration(new Configuration(), [$yaml['xm_symfony']]);

        return new TokenScrubber(
            $config['sentry_scrubber']['paths'],
            $config['sentry_scrubber']['query_params'],
            $config['sentry_scrubber']['patterns'],
        );
    }
}
