<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\User\Command\AdminAddUserMinimum;
use App\Model\User\Command\GenerateUserToken;
use App\Model\User\Name;
use App\Model\User\Role;
use App\Model\User\User;
use App\Model\User\UserId;
use App\Projection\User\UserTokenFinder;
use App\Security\PasswordHasher;
use App\Security\TokenGenerator;
use App\Util\Assert;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\RFCValidation;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Util\Json;

final class AddUserCommand extends Command
{
    private MessageBusInterface $commandBus;
    private PasswordHasher $passwordHasher;
    private TokenGenerator $tokenGenerator;
    private UserTokenFinder $userTokenFinder;
    private RouterInterface $router;

    private const SEND_INVITE = 'send-invite';
    private const GENERATE_ACTIVATION_TOKEN = 'generate-activation-token';
    private const FORMAT = 'format';

    public function __construct(
        MessageBusInterface $commandBus,
        PasswordHasher $passwordHasher,
        TokenGenerator $tokenGenerator,
        UserTokenFinder $userTokenFinder,
        RouterInterface $router,
    ) {
        $this->commandBus = $commandBus;
        $this->passwordHasher = $passwordHasher;
        $this->tokenGenerator = $tokenGenerator;
        $this->userTokenFinder = $userTokenFinder;
        $this->router = $router;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('app:user:add')
            ->setDescription('Add a user.')
            ->addOption(
                self::SEND_INVITE,
                null,
                InputOption::VALUE_NONE,
                'Send the user an invite instead of asking for a password.',
            )
            ->addOption(
                self::GENERATE_ACTIVATION_TOKEN,
                null,
                InputOption::VALUE_NONE,
                'Generates the activation token and either displays or returns it. Cannot be used with send-invite.',
            )
            ->addOption(
                self::FORMAT,
                null,
                InputOption::VALUE_REQUIRED,
                'Determines the format of the output. Options: "default" or "json"',
                'default',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Create New User');

        $sendInvite = $input->getOption(self::SEND_INVITE);
        $generateActivationToken = $input->getOption(self::GENERATE_ACTIVATION_TOKEN);

        if ($sendInvite && $generateActivationToken) {
            throw new InvalidArgumentException(sprintf('%s and %s cannot be used together.', self::SEND_INVITE, self::GENERATE_ACTIVATION_TOKEN));
        }

        $email = Email::fromString($this->askForEmail($io));
        if (!$sendInvite || $generateActivationToken) {
            $password = $this->askForPassword($io);
        } else {
            $password = ($this->tokenGenerator)()->toString();
        }
        $role = Role::byValue($this->askForRole($io));
        $firstName = Name::fromString($this->askForName('First name', $io));
        $lastName = Name::fromString($this->askForName('Last name', $io));

        $userId = UserId::fromUuid(Uuid::uuid4());

        $this->commandBus->dispatch(
            AdminAddUserMinimum::with(
                $userId,
                $email,
                ($this->passwordHasher)($role, $password),
                $role,
                $firstName,
                $lastName,
                $sendInvite,
            ),
        );

        if ($generateActivationToken) {
            $this->commandBus->dispatch(GenerateUserToken::now($userId));

            $activationToken = $this->userTokenFinder->findOneBy(['user' => $userId])->token();
            $resetUrl = $this->router->generate(
                'user_reset',
                ['token' => $activationToken],
                UrlGeneratorInterface::ABSOLUTE_URL,
            );
        }

        if ('json' === $input->getOption(self::FORMAT)) {
            $results = [
                'userId' => $userId->toString(),
                'email'  => $email->toString(),
                'role'   => $role->getValue(),
            ];

            if ($generateActivationToken) {
                $results['activationToken'] = $activationToken->toString();
                $results['resetUrl'] = $resetUrl;
            }

            $io->write(Json::encode($results));

            return self::SUCCESS;
        }

        $io->writeln(
            sprintf(
                'Created new active user %s with role %s with ID: %s.',
                $email,
                $role->getValue(),
                $userId,
            ),
        );

        if ($sendInvite) {
            $io->writeln(sprintf('Invite sent to %s.', $email));
        }
        if ($generateActivationToken) {
            $io->writeln(sprintf('Activation token %s.', $activationToken));
            $io->writeln(sprintf('Reset URL %s.', $resetUrl));
        }

        return self::SUCCESS;
    }

    private function askForEmail(SymfonyStyle $io): string
    {
        return $io->ask('Email', null, function (?string $email): string {
            Assert::notEmpty($email, 'A valid email address is required.');

            Assert::true(
                (new EmailValidator())->isValid($email, new RFCValidation()),
                'The email address in invalid.',
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
        return $io->choice('Role', Role::getValues(), 'ROLE_USER');
    }

    private function askForName(string $label, SymfonyStyle $io): string
    {
        return $io->ask($label, null, function (?string $name): string {
            Name::fromString($name);

            return trim($name);
        });
    }
}
