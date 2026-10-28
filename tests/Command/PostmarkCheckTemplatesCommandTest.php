<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\PostmarkCheckTemplatesCommand;
use App\Infrastructure\Email\EmailTemplate;
use App\Tests\BaseTestCase;
use Postmark\Models\PostmarkException;
use Postmark\Models\PostmarkTemplateList;
use Postmark\PostmarkClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class PostmarkCheckTemplatesCommandTest extends BaseTestCase
{
    public function testAllTemplates(): void
    {
        $commandTester = $this->commandTester($this->templates($this->aliases()));

        $this->assertEquals(Command::SUCCESS, $commandTester->execute([]));
        $this->assertStringContainsString('Postmark has all', $commandTester->getDisplay());
    }

    /**
     * A template in Postmark the platform doesn't use isn't a problem.
     */
    public function testExtraTemplate(): void
    {
        $commandTester = $this->commandTester(
            $this->templates([...$this->aliases(), $this->faker()->slug()]),
        );

        $this->assertEquals(Command::SUCCESS, $commandTester->execute([]));
    }

    public function testMissingTemplate(): void
    {
        $aliases = $this->aliases();
        $missing = $this->faker()->randomElement($aliases);

        $commandTester = $this->commandTester(
            $this->templates(array_values(array_diff($aliases, [$missing]))),
        );

        $this->assertEquals(Command::FAILURE, $commandTester->execute([]));
        $this->assertStringContainsString($missing.' is missing.', $commandTester->getDisplay());
    }

    public function testInactiveTemplate(): void
    {
        $aliases = $this->aliases();
        $inactive = $this->faker()->randomElement($aliases);

        $commandTester = $this->commandTester($this->templates($aliases, [$inactive]));

        $this->assertEquals(Command::FAILURE, $commandTester->execute([]));
        $this->assertStringContainsString($inactive.' is not active.', $commandTester->getDisplay());
    }

    /**
     * Templates past the first page are still found.
     */
    public function testPaged(): void
    {
        // pretend the first page was full: Postmark says there are more than it returned
        $total = 500 + \count($this->aliases());
        $half = intdiv(\count($this->aliases()), 2);

        $postmarkClient = \Mockery::mock(PostmarkClient::class);
        $postmarkClient->shouldReceive('listTemplates')
            ->once()
            ->with(500, 0, 'Standard')
            ->andReturn($this->templateList(\array_slice($this->aliases(), 0, $half), $total));
        $postmarkClient->shouldReceive('listTemplates')
            ->once()
            ->with(500, 500, 'Standard')
            ->andReturn($this->templateList(\array_slice($this->aliases(), $half), $total));

        $commandTester = new CommandTester(new PostmarkCheckTemplatesCommand($postmarkClient));

        $this->assertEquals(Command::SUCCESS, $commandTester->execute([]));
    }

    public function testPostmarkCantBeRead(): void
    {
        $postmarkClient = \Mockery::mock(PostmarkClient::class);
        $postmarkClient->shouldReceive('listTemplates')
            ->once()
            ->andThrow(new PostmarkException('Invalid token.'));

        $commandTester = new CommandTester(new PostmarkCheckTemplatesCommand($postmarkClient));

        $this->assertEquals(Command::FAILURE, $commandTester->execute([]));
        $this->assertStringContainsString('could not be read from Postmark', $commandTester->getDisplay());
    }

    /**
     * @return list<string>
     */
    private function aliases(): array
    {
        return array_column(EmailTemplate::cases(), 'value');
    }

    /**
     * @param list<string> $aliases
     * @param list<string> $inactive
     */
    private function templates(array $aliases, array $inactive = []): PostmarkTemplateList
    {
        return $this->templateList($aliases, \count($aliases), $inactive);
    }

    /**
     * @param list<string> $aliases
     * @param list<string> $inactive
     */
    private function templateList(array $aliases, int $total, array $inactive = []): PostmarkTemplateList
    {
        return new PostmarkTemplateList([
            'TotalCount' => $total,
            'Templates'  => array_map(
                fn (string $alias): array => [
                    'TemplateId' => $this->faker()->randomNumber(8),
                    'Alias'      => $alias,
                    'Name'       => $this->faker()->words(3, true),
                    'Active'     => !\in_array($alias, $inactive, true),
                ],
                $aliases,
            ),
        ]);
    }

    private function commandTester(PostmarkTemplateList $templates): CommandTester
    {
        $postmarkClient = \Mockery::mock(PostmarkClient::class);
        $postmarkClient->shouldReceive('listTemplates')
            ->once()
            ->with(500, 0, 'Standard')
            ->andReturn($templates);

        return new CommandTester(new PostmarkCheckTemplatesCommand($postmarkClient));
    }
}
