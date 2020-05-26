<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\Page\Command\AddPage;
use App\Model\Page\Command\PublishPage;
use App\Model\Page\Content;
use App\Model\Page\PageId;
use App\Model\Page\Path;
use App\Model\Page\Template;
use App\Model\Page\Title;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

final class AddDefaultPagesCommand extends Command
{
    /** @var MessageBusInterface */
    private $commandBus;

    public function __construct(MessageBusInterface $commandBus)
    {
        $this->commandBus = $commandBus;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('app:content:add-default')
            ->setDescription('Add the default content.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Creating default content');

        $defaultContent = Content::createDefaultContent()->toArray();

        $pageId = PageId::fromUuid(Uuid::uuid4());
        $this->commandBus->dispatch(
            AddPage::to(
                $pageId,
                Path::fromUserString('/'),
                Template::fromString('default/index.html.twig'),
                Title::fromString('Homepage'),
                Content::createDefaultContent()
            )
        );
        $this->commandBus->dispatch(PublishPage::now($pageId));

        $pageId = PageId::fromUuid(Uuid::uuid4());
        $this->commandBus->dispatch(
            AddPage::to(
                $pageId,
                Path::fromUserString('/about-us'),
                Template::fromString('static.html.twig'),
                Title::fromString('About Us'),
                Content::fromArray([
                    'pageTitle' => [
                        'type'  => 'text',
                        'value' => 'About Us',
                    ],
                    'content'   => [
                        'type'  => 'html',
                        'value' => '<p>All about the company...</p>',
                    ],
                ] + $defaultContent)
            )
        );
        $this->commandBus->dispatch(PublishPage::now($pageId));

        $pageId = PageId::fromUuid(Uuid::uuid4());
        $this->commandBus->dispatch(
            AddPage::to(
                $pageId,
                Path::fromUserString('/services'),
                Template::fromString('static.html.twig'),
                Title::fromString('Services'),
                Content::fromArray([
                    'pageTitle' => [
                        'type'  => 'text',
                        'value' => 'Services',
                    ],
                    'content'   => [
                        'type'  => 'html',
                        'value' => '<p>We provide the following services...</p>',
                    ],
                ] + $defaultContent)
            )
        );
        $this->commandBus->dispatch(PublishPage::now($pageId));

        $pageId = PageId::fromUuid(Uuid::uuid4());
        $this->commandBus->dispatch(
            AddPage::to(
                $pageId,
                Path::fromUserString('/services/web-development'),
                Template::fromString('static.html.twig'),
                Title::fromString('Web Development'),
                Content::fromArray([
                    'pageTitle' => [
                        'type'  => 'text',
                        'value' => 'Web Development',
                    ],
                    'content'   => [
                        'type'  => 'html',
                        'value' => '<p>We build websites...</p>',
                    ],
                ] + $defaultContent)
            )
        );
        $this->commandBus->dispatch(PublishPage::now($pageId));

        $io->success('Default content added');

        return 0;
    }
}
