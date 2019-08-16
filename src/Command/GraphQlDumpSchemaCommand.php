<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\Email;
use App\Projection\User\UserFinder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

final class GraphQlDumpSchemaCommand extends Command
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var UserFinder */
    private $userFinder;

    /**
     * @var string the email of the user to use for the fake login (typically
     *             an admin)
     * @todo-symfony change to super admin user
     */
    private $userEmail = 'admin@example.com';

    public function __construct(
        TokenStorageInterface $tokenStorage,
        UserFinder $userFinder
    ) {
        parent::__construct();

        $this->tokenStorage = $tokenStorage;
        $this->userFinder = $userFinder;
    }

    protected function configure(): void
    {
        $this->setName('app:graphql:dump-schema')
            ->setDescription('Dumps GraphQL schema')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->tokenStorage->setToken($this->token());

        $command = $this->getApplication()->find('graphql:dump-schema');

        $arguments = [
            'command'  => 'graphql:dump-schema',
            '--file'   => 'graphql.schema.json',
            '--modern' => true,
        ];

        return $command->run(new ArrayInput($arguments), $output);
    }

    private function token(): PostAuthenticationGuardToken
    {
        $user = $this->userFinder->findOneByEmail(
            Email::fromString($this->userEmail)
        );

        if (!$user) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The user with email %s cannot be found.',
                    $this->userEmail
                )
            );
        }

        return new PostAuthenticationGuardToken(
            $user,
            'app_provider',
            $user->roles()
        );
    }
}
