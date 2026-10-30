<?php

declare(strict_types=1);

namespace App\Command;

use App\Infrastructure\Email\EmailTemplate;
use Postmark\Models\PostmarkException;
use Postmark\PostmarkClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
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
final class PostmarkCheckTemplatesCommand extends Command
{
    // the most Postmark returns at a time
    private const int PAGE_SIZE = 500;

    public function __construct(
        #[AutowireInline(PostmarkClient::class, ['$serverToken' => '%env(POSTMARK_API_KEY)%'])]
        private readonly PostmarkClient $postmarkClient,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Postmark Template Check');

        try {
            $templates = $this->templates();
        } catch (PostmarkException $e) {
            $io->error(\sprintf('The templates could not be read from Postmark: %s', $e->getMessage()));

            return self::FAILURE;
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

            return self::SUCCESS;
        }

        $io->listing($problems);
        $io->error('Postmark is missing templates.');

        return self::FAILURE;
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
