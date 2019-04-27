<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\Email;
use App\Model\User\Command\AdminAddUserMinimum;
use App\Model\User\Role;
use App\Model\User\User;
use App\Model\User\UserId;
use App\Security\PasswordEncoder;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\RFCValidation;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Encoder\BasePasswordEncoder;
use Webmozart\Assert\Assert;

final class AddUserCommand extends Command
{
    /** @var MessageBusInterface */
    private $commandBus;

    /** @var PasswordEncoder */
    private $passwordEncoder;

    public function __construct(
        MessageBusInterface $commandBus,
        PasswordEncoder $passwordEncoder
    ) {
        parent::__construct();

        $this->commandBus = $commandBus;
        $this->passwordEncoder = $passwordEncoder;
    }

    protected function configure()
    {
        $this->setName('app:user:add')
            ->setDescription('Add a user.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Create New User');

        $email = Email::fromString($this->askForEmail($io));
        $password = $this->askForPassword($io);
        $role = Role::byValue($this->askForRole($io));

        $encodedPassword = ($this->passwordEncoder)($role, $password);

        $userId = UserId::fromUuid(Uuid::uuid4());

        $this->commandBus->dispatch(AdminAddUserMinimum::with(
            $userId,
            $email,
            $encodedPassword,
            $role
        ));

        $io->writeln(
            sprintf(
                'Created new active user %s with role %s with ID: %s.',
                $email,
                $role->getValue(),
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
        return $io->choice('Role', Role::getValues(), 'ROLE_USER');
    }
}
