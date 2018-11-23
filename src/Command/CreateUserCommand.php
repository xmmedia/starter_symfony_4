<?php

declare(strict_types=1);

namespace App\Command;

use App\DataProvider\RoleProvider;
use App\Model\Email;
use App\Model\User\Command\AdminCreateUserMinimum;
use App\Model\User\User;
use App\Model\User\UserId;
use App\Security\PasswordEncoder;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\RFCValidation;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Encoder\BasePasswordEncoder;
use Symfony\Component\Security\Core\Role\Role;
use Webmozart\Assert\Assert;

final class CreateUserCommand extends ContainerAwareCommand
{
    /** @var MessageBusInterface */
    private $commandBus;

    /** @var RoleProvider */
    private $roleProvider;

    /** @var PasswordEncoder */
    private $passwordEncoder;

    public function __construct(
        MessageBusInterface $commandBus,
        RoleProvider $roleProvider,
        PasswordEncoder $passwordEncoder
    ) {
        parent::__construct();

        $this->commandBus = $commandBus;
        $this->roleProvider = $roleProvider;
        $this->passwordEncoder = $passwordEncoder;
    }

    protected function configure()
    {
        $this->setName('app:user:create')
            ->setDescription('Create a user.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Create New User');

        $email = Email::fromString($this->askForEmail($io));
        $password = $this->askForPassword($io);
        $role = new Role($this->askForRole($io));

        $encodedPassword = ($this->passwordEncoder)($role, $password);

        $userId = UserId::generate();

        $this->commandBus->dispatch(AdminCreateUserMinimum::withData(
            $userId,
            $email,
            $encodedPassword,
            $role
        ));

        $io->writeln(
            sprintf(
                'Created new active user %s with role %s with ID: %s.',
                $email,
                $role->getRole(),
                $userId->toString()
            )
        );
    }

    private function askForEmail(SymfonyStyle $io): string
    {
        return $io->ask('Email', null, function (?string $email): string {
            Assert::notEmpty($email, 'A valid email address is required.');

            Assert::true(
                (new EmailValidator())->isValid($email, new RFCValidation()),
                'The email address in invalid.'
            );

            return trim($email);
        });
    }

    private function askForPassword(SymfonyStyle $io): string
    {
        return $io->askHidden('Password', function (?string $password): string {
            Assert::notEmpty($password, 'A password is required.');
            Assert::minLength(
                $password,
                User::PASSWORD_MIN_LENGTH,
                'The password must be at least %2$d characters long.'
            );
            Assert::maxLength(
                $password,
                BasePasswordEncoder::MAX_PASSWORD_LENGTH,
                'The password must be less than %2$d characters long.'
            );

            return $password;
        });
    }

    private function askForRole(SymfonyStyle $io): string
    {
        return $io->choice('Role', ($this->roleProvider)(), 'ROLE_USER');
    }
}
