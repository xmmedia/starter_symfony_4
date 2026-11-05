<?php

declare(strict_types=1);

namespace App\Command;

use App\Infrastructure\Service\UserPasswordStore;
use App\Model\User\Command\AdminAddUserMinimum;
use App\Model\User\Name;
use App\Model\User\Role;
use App\Model\User\User;
use App\Model\User\UserId;
use App\Projection\User\UserFinder;
use App\Security\PasswordHasher;
use App\Util\Assert;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\RFCValidation;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Util\Json;

#[AsCommand(
    name: 'app:user:add',
    description: 'Add a user.',
)]
final readonly class AddUserCommand
{
    private const array FORMATS = ['default', 'json'];

    public function __construct(
        private MessageBusInterface $commandBus,
        private PasswordHasher $passwordHasher,
        private UserPasswordStore $passwordStore,
        private UserFinder $userFinder,
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private RouterInterface $router,
    ) {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Option('Send the user an invite instead of asking for a password.')]
        bool $sendInvite = false,
        #[Option('Generates the activation token and either displays or returns it. Cannot be used with send-invite.')]
        bool $generateActivationToken = false,
        #[Option(
            'Determines the format of the output. Options: "default" or "json"',
            suggestedValues: self::FORMATS,
        )]
        string $format = 'default',
    ): int {
        $io->title('Create New User');

        if ($sendInvite && $generateActivationToken) {
            throw new InvalidArgumentException('send-invite and generate-activation-token cannot be used together.');
        }

        if (!\in_array($format, self::FORMATS, true)) {
            throw new InvalidArgumentException(
                \sprintf('The format "%s" is not supported. Use one of: %s.', $format, implode(', ', self::FORMATS)),
            );
        }

        $email = Email::fromString($this->askForEmail($io));
        if (!$sendInvite) {
            $password = $this->askForPassword($io);
        } else {
            // a random password with some of the extras removed/trimmed off
            $password = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        }
        $role = Role::from($this->askForRole($io));
        $firstName = Name::fromString($this->askForName('First name', $io));
        $lastName = Name::fromString($this->askForName('Last name', $io));

        $userId = UserId::fromUuid(Uuid::uuid4());

        $this->commandBus->dispatch(
            AdminAddUserMinimum::with(
                $userId,
                $email,
                $role,
                $firstName,
                $lastName,
                $sendInvite,
            ),
        );

        // the hash is deliberately not part of the command: stored after the
        // user exists so a rejected command doesn't leave a stray credential
        $this->passwordStore->store($userId, ($this->passwordHasher)($role, $password));

        if ($generateActivationToken) {
            $activationToken = $this->resetPasswordHelper->generateResetToken($this->userFinder->find($userId));

            $resetUrl = $this->router->generate(
                'user_reset_token',
                ['token' => $activationToken->getToken()],
                UrlGeneratorInterface::ABSOLUTE_URL,
            );
        }

        if ('json' === $format) {
            $results = [
                'userId' => $userId->toString(),
                'email'  => $email->toString(),
                'role'   => $role->value,
            ];

            if ($generateActivationToken) {
                $results['activationToken'] = $activationToken->getToken();
                $results['resetUrl'] = $resetUrl;
            }

            $io->write(Json::encode($results), false, OutputInterface::OUTPUT_RAW);

            return Command::SUCCESS;
        }

        $io->writeln(
            \sprintf(
                'Created new active user %s with role %s with ID: %s.',
                $email,
                $role->value,
                $userId,
            ),
        );

        if ($sendInvite) {
            $io->writeln(\sprintf('Invite sent to %s.', $email));
        }
        if ($generateActivationToken) {
            $io->writeln(\sprintf('Activation token %s.', $activationToken->getToken()));
            $io->writeln(\sprintf('Reset URL %s.', $resetUrl));
        }

        return Command::SUCCESS;
    }

    private function askForEmail(SymfonyStyle $io): string
    {
        return $io->ask('Email', null, static function (?string $email): string {
            Assert::notEmpty($email, 'A valid email address is required.');

            Assert::true(
                new EmailValidator()->isValid($email, new RFCValidation()),
                'The email address in invalid.',
            );

            return trim($email);
        });
    }

    private function askForPassword(SymfonyStyle $io): string
    {
        return $io->askHidden('Password', static function (#[\SensitiveParameter] ?string $password): string {
            Assert::notEmpty($password, 'A password is required.');
            Assert::minLength(
                $password,
                User::PASSWORD_MIN_LENGTH,
                'The password must be at least %2$d characters long.',
            );
            Assert::maxLength(
                $password,
                PasswordHasherInterface::MAX_PASSWORD_LENGTH,
                'The password must be less than %2$d characters long.',
            );

            return $password;
        });
    }

    private function askForRole(SymfonyStyle $io): string
    {
        return $io->choice(
            'Role',
            array_values(array_diff(Role::values(), [Role::ROLE_ALLOWED_TO_SWITCH->value])),
            'ROLE_USER',
        );
    }

    private function askForName(string $label, SymfonyStyle $io): string
    {
        return $io->ask($label, null, static function (?string $name): string {
            Name::fromString($name);

            return trim($name);
        });
    }
}
