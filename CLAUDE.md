# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Symfony 7 starter template for creating web applications at XM Media. It uses Event Sourcing with CQRS patterns via Prooph, GraphQL for the API layer, and Vue 3 for the frontend.

**Key architectural patterns:**
- Event Sourcing with CQRS for domain logic
- GraphQL API (via OverblogGraphQLBundle)
- Vue 3 SPA with Vue Router and Pinia for state management
- Read Model Projections for querying data
- Process Managers for cross-aggregate workflows

## Coding Standards

- Follow existing project structure and patterns for new features.
- Avoid use of temporary variables unless it makes it easier to understand.
- Use `$this->faker()` in tests instead of hardcoding values.
- Follow PSR-12 coding standards.
- Use type hints and return types wherever possible.
- Avoid use of ternary operators except for very simple cases.
- Use Symfony service auto wiring and autoconfiguration, instead of manually configuring services.
- Write tests for new features and bug fixes.

## Development Commands

### Running Tests
- All PHP tests: `lando composer test` or `composer test`
- Single PHP test: `bin/phpunit tests/Path/To/TestFile.php`
- Test with coverage (HTML): `composer test:coverage`
- Show deprecations: `composer test:deprecations`
- JS/Frontend tests: `yarn test:unit` – not working at the moment

### Code Quality & Linting
- Run all checks: `bin/check`
- Run all checks with auto-fixes: `bin/check_full` (runs rector, php-cs-fixer, then bin/check)
- PHP CS (dry run): `lando composer cs` or `composer cs`
- PHP CS (fix): `lando composer cs:fix` or `composer cs:fix`
- PHPStan static analysis: `lando composer static` or `composer static`
- Rector (dry run): `lando composer rector` or `composer rector`
- Rector (fix): `lando composer rector:fix` or `composer rector:fix`
- Lint JS: `yarn lint:js` or `yarn lint:js:fix`
- Lint CSS: `yarn lint:css` or `yarn lint:css:fix`
- Lint YAML: `lando console lint:yaml config`
- Lint Twig: `lando console lint:twig templates`
- Lint container: `lando console lint:container`

### Building Frontend Assets
- Development build with watch: `yarn dev` (recommended: `nvm use && yarn && yarn dev`)
- Production build: `yarn build`
- Preview production build: `yarn preview`

### Package Management
- Install PHP packages: `lando composer install` or `composer install`
- Install JS packages: `yarn`
- Upgrade all JS packages: `yarn up -R "**"`
- Upgrade specific JS package: `yarn up -R "package-name"`
- Upgrade JS packages (interactive for major versions): `yarn upgrade-interactive`

### GraphQL Schema
- Generate GraphQL schema: `bin/generate_schema` (runs `lando console graphql:dump-schema --with-descriptions --format=graphql`)
- Access GraphiQL (dev only): `/graphiql`

### Event Store & Projections
- Create event stream: `lando console event-store:event-stream:create <stream_name>`
- Run projection once: `lando console event-store:projection:run <projection_name> -o`
- Show all projection commands: `lando console event-store:projection`

Existing event streams: `user`, `auth`
Existing projections: `user_projection`, `auth_projection`

### Makers
- Make aggregate root/model: `bin/console make:model` or `lando console make:model`
- Make projection: `bin/console make:projection` or `lando console make:projection`

### User Management
- Add a user: `bin/console app:user:add` or `lando console app:user:add` (select `ROLE_SUPER_ADMIN` for admin access)

### Lando Commands
- Start Lando: `lando start`
- Install PHP packages: `lando composer install`
- Run Symfony console: `lando console <command>`
- Import database: `lando db-import <file.sql>`
- Enable Xdebug: `lando xdebug-on`
- Disable Xdebug: `lando xdebug-off`

## Architecture

### Event Sourcing & CQRS

**Aggregate Roots** (`src/Model/*/`)
- Domain entities that emit events (extend `AggregateRoot`)
- Examples: `User`, `Auth`
- Only aggregates can change state by recording events
- Located in `src/Model/<Domain>/` (e.g., `src/Model/User/User.php`)

**Commands** (`src/Model/*/Command/`)
- Represent user intentions (e.g., `AdminAddUser`, `ChangePassword`)
- Handled by Command Handlers in `src/Model/*/Handler/`
- Commands are routed through Symfony Messenger (`messenger.bus.commands`)

**Events** (`src/Model/*/Event/`)
- Immutable facts about what happened (e.g., `UserWasAddedByAdmin`, `ChangedPassword`)
- Stored in event streams (configured in `config/packages/event_sourcing.yaml`)
- Processed by Projections and Process Managers via event bus (`messenger.bus.events`)

**Projections** (`src/Projection/*/`)
- Read models built from events (implement `ReadModelProjection`)
- Examples: `UserProjection`, `AuthProjection`
- Automatically run via `RunProjectionMiddleware` on event bus
- Create tables defined in `src/Projection/Table.php` and `src/Projection/*/ReadModel.php`

**Process Managers** (`src/ProcessManager/`)
- Coordinate cross-aggregate workflows triggered by events
- Examples: `UserInviteProcessManager`, `ChangedPasswordProcessManager`
- Listen to events and dispatch new commands

**Repositories** (`src/Infrastructure/Repository/`)
- Load and save aggregate roots to/from event streams
- Configured in `config/packages/event_sourcing.yaml`

### GraphQL Layer

**Structure:**
- Schema types: `config/graphql/types/`
- Queries: `src/GraphQl/Query/`
- Mutations: `src/GraphQl/Mutation/`
- Custom types: `src/GraphQl/Type/`

**Frontend GraphQL:**
- Query/mutation files: `public/js/src/*/queries/*.graphql`
- Loaded via `@rollup/plugin-graphql`

### Frontend Architecture

**Entry Points:**
- Admin app: `public/js/src/admin.js`
- User app: `public/js/src/user.js`

**Structure:**
- Components: `public/js/src/admin/`, `public/js/src/user/`, `public/js/src/common/`
- Routers: `public/js/src/admin/router.js`, `public/js/src/user/router.js`
- State stores (Pinia): `public/js/src/admin/stores/`, `public/js/src/user/stores/`
- Validators (Vuelidate): Files ending in `.validation.js`
- State machines (XState): `public/js/src/common/state_machines.js`

**Apollo Client:**
- Configuration: `public/js/src/common/apollo.js`
- Used via `@vue/apollo-composable`

## Code Style

### PHP

This project uses PHP CS Fixer with `@Symfony` standards. Key patterns enforced:

**Yoda Conditions:**
Use Yoda-style conditionals for comparisons with null, booleans, and constants:
```php
// ✅ Correct
if (null === $value) { }
if (false === $flag) { }
if (200 === $statusCode) { }

// ❌ Incorrect
if ($value === null) { }
if ($flag === false) { }
if ($statusCode === 200) { }
```

**Avoid Temporary Variables:**
Call methods directly instead of storing in temporary variables when the variable is only used once:
```php
// ✅ Correct - direct method calls
$member = Member::add(
    $command->memberId(),
    $command->membershipNumber(),
    $command->firstName(),
    // ...
);
$this->memberRepo->save($member);

// ❌ Incorrect - unnecessary temporary variable
$memberId = $command->memberId();
$membershipNumber = $command->membershipNumber();
$member = Member::add($memberId, $membershipNumber, ...);
```

**Exception:** Use temporary variables when they improve readability for complex expressions or when the value is used multiple times.

**Other PHP Standards:**
- `declare(strict_types=1);` at the top of every PHP file
- Constructor property promotion (PHP 8.0+)
- Short array syntax `[]` not `array()`
- Trailing commas in multiline arrays, arguments, and parameters
- Ordered imports alphabetically
- Binary operator alignment for `=>` in arrays
- Use Doctrine attributes for entity mapping
- Don't use comments unless necessary; prefer self-explanatory code
- Use VOs to encapsulate primitive types & pass domain concepts
- Use FakerPHP to generate test data, including the UuidFakerProvider
- Keep line length to 120 characters unless it makes code less readable

Run `lando composer cs:fix` to auto-fix most style issues.

### JavaScript/Vue

ESLint enforces code style. Key rules:

**General:**
- Max line length: 120 characters
- Trailing commas in multiline arrays/objects
- Space before function parentheses: `function name () { }`
- No `console.log` or `debugger` in committed code
- Add a blank line before a return statement unless directly inside a conditional

**Vue Specific:**
- 4-space indentation for HTML templates
- Max 3 attributes per line for single-line tags
- 1 attribute per line for multiline (first attribute on same line allowed)
- Component self-closing: always for components, optional for HTML elements
- No newline before closing bracket in multiline tags
- Component names: kebab-case

Run `yarn lint:js:fix` and `yarn lint:css:fix` to auto-fix style issues.

## Important Notes

- **Never modify aggregates directly** - always use commands/handlers
- **Projections are eventually consistent** - run projection commands after event changes
- **Don't bypass event sourcing** - read from read models, write via commands
- **Test coverage required** - especially for aggregates and handlers
- **Use type hints** - strict types are declared in all PHP files
- **Branch**: Work on `v2` branch for production deployments
- **Memory**: Some operations (tests, projections) may need `php -d memory_limit=-1`

### User Model

**User States:**

**Verified** - Email address is verified
- When user is added by admin: Always set to `true`
- When user registers themselves: Set to `false` until they verify their email

**Active** - User is active and can log in
- When user is added by admin: Set to `true` when they're sent an invitation (invite) or `false` until they activate their account & set their password
- When user registers themselves: Set to `true`

**Key Actions:**
- **Send Activation**: Sends email to user to activate their account by entering their password. Uses a reset token and sets their account to active when complete.
- **Send Verification**: For email/user verification after registering. Sends email to user to activate their account. They don't need to enter a password (already set on registration form). Uses a reset token and sets their account to verified when complete.

## Tech Stack

**Backend:**
- Symfony 7.3 on PHP 8.4
- Prooph PDO Event Store for Event Sourcing
- OverblogGraphQLBundle for GraphQL API
- Doctrine ORM for read models only (not domain models)
- Symfony Messenger for command/event buses
- Postmark for email delivery

**Frontend:**
- Vue 3 with Composition API
- Vite for build tooling
- Apollo Client for GraphQL
- Pinia for state management
- Vuelidate for form validation
- XState for state machines
- Tailwind CSS for styling

**Local Development:**
- Lando (Docker-based local dev environment)
- Node 22 with Yarn v4
- MySQL 8.0

## Configuration Notes

- Environment config: Copy `.env.local-default` to `.env.local` and update `@todo-symfony` values
- Lando site name is in `.lando.yml` (default: `symfonystarter`)
- Vite dev server port in `vite.config.mjs` (default: 9008)
- Database collation should be `utf8mb4_bin`

## Maintenance

### Updating PHP Version
When upgrading PHP, update version in these files:
- `composer.json` - add polyfill for new version
- `.lando.yml`
- `setup_dev.sh`, `setup_prod.sh`, `.gitlab-ci.yml`
- `.php-cs-fixer.dist.php`

Then run: `lando rebuild && lando composer update && nvm use && bin/check_full`

Write conditionals in yoda style.
