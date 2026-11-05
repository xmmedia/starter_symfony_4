<?php

declare(strict_types=1);

namespace App\Command;

use App\Infrastructure\Email\EmailTemplate;
use Postmark\Models\PostmarkException;
use Postmark\PostmarkClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\AutowireInline;

/**
 * Checks that the Postmark server has an active template for every EmailTemplate. Run at
 * the end of a deploy: an email whose template is missing fails when it's sent.
 */
#[AsCommand(
    name: 'app:postmark:check-templates',
    description: 'Checks Postmark has an active template for every email the platform sends.',
)]
final readonly class PostmarkCheckTemplatesCommand
{
    // the most Postmark returns at a time
    private const int PAGE_SIZE = 500;

    public function __construct(
        #[AutowireInline(PostmarkClient::class, ['$serverToken' => '%env(POSTMARK_API_KEY)%'])]
        private PostmarkClient $postmarkClient,
    ) {
    }

    public function __invoke(SymfonyStyle $io): int
    {
        $io->title('Postmark Template Check');

        try {
            $templates = $this->templates();
        } catch (PostmarkException $e) {
            $io->error(\sprintf('The templates could not be read from Postmark: %s', $e->getMessage()));

            return Command::FAILURE;
        }

        $problems = [];
        foreach (EmailTemplate::cases() as $emailTemplate) {
            if (!\array_key_exists($emailTemplate->value, $templates)) {
                $problems[] = \sprintf('%s is missing.', $emailTemplate->value);
            } elseif (!$templates[$emailTemplate->value]->isActive()) {
                $problems[] = \sprintf('%s is not active.', $emailTemplate->value);
            }
        }

        if ([] === $problems) {
            $io->success(\sprintf('Postmark has all %d templates.', \count(EmailTemplate::cases())));

            return Command::SUCCESS;
        }

        $io->listing($problems);
        $io->error('Postmark is missing templates.');

        return Command::FAILURE;
    }

    /**
     * @return array<string, \Postmark\Models\PostmarkTemplate> keyed by alias
     *
     * @throws PostmarkException
     */
    private function templates(): array
    {
        $templates = [];
        $offset = 0;

        do {
            $list = $this->postmarkClient->listTemplates(self::PAGE_SIZE, $offset, 'Standard');
            foreach ($list->getTemplates() as $template) {
                $templates[$template->getAlias()] = $template;
            }
            $offset += self::PAGE_SIZE;
        } while ($offset < $list->getTotalCount());

        return $templates;
    }
}
