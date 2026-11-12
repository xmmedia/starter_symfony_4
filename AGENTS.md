# AGENTS.md

This file provides guidance to agents when working with code in this repository.

## Auto-generated Files — Do Not Edit

- `schema.graphql` — auto-generated from YAML config in `config/graphql/types/`. Never edit this file directly.
- `config/reference.php` — auto-generated. Never edit this file directly.

## Project Overview

This is a Symfony 8 starter template for creating web applications at XM Media. It uses Event Sourcing with CQRS patterns via Prooph, GraphQL for the API layer, and Vue 3 for the frontend.

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
- Avoid named arguments unless they make the call clearer — skipping over a run of optional
  parameters, or disambiguating consecutive booleans. Otherwise pass positionally.
- Use Symfony service auto wiring and autoconfiguration, instead of manually configuring services.
  Where a type hint can't express it, stay in the class with `#[Autowire]` (parameters, env vars)
  or `#[Target]` (one of several implementations), and use `#[AsAlias]`/`#[AsTaggedItem]`/
  `#[Autoconfigure]` before reaching for `services.yaml`.
- Before hand-writing infrastructure (locks, caches, HTTP clients, schedulers) or adding a library,
  check whether a Symfony component covers it. Use `symfony/lock` (`LockFactory`) for mutual
  exclusion, not a hand-built flag or lock file.
- Write tests for new features and bug fixes. A feature isn't done until a test exercises it the way
  a caller would (a GraphQL request for a query/mutation, a dispatched command for a handler), not
  just "it didn't throw".

## Development Commands

### Running Tests
- All PHP tests: `ddev composer test` or `composer test`
- Single PHP test: `bin/phpunit tests/Path/To/TestFile.php`
- Test with coverage (HTML): `ddev composer test:coverage` (PCOV, so Xdebug can stay off)
- Show deprecations: `composer test:deprecations`
- JS/Frontend tests: `yarn test:unit` – not working at the moment

### Code Quality & Linting
- Run all checks: `bin/check_full` (runs composer normalize, rector, php-cs-fixer, bin/generate_schema, then bin/check)
- PHP CS (dry run): `ddev composer cs` or `composer cs`
- PHP CS (fix): `ddev composer cs:fix` or `composer cs:fix`
- PHPStan static analysis: `ddev composer static` or `composer static`
- Rector (dry run): `ddev composer rector` or `composer rector`
- Rector (fix): `ddev composer rector:fix` or `composer rector:fix`
- Lint JS: `ddev yarn lint:js` or `ddev yarn lint:js:fix`
- Lint CSS: `ddev yarn lint:css` or `ddev yarn lint:css:fix`
- Lint YAML: `ddev console lint:yaml config`
- Lint Twig: `ddev console lint:twig templates`
- Lint container: `ddev console lint:container`
- Symfony Language Tools (Symfony-aware diagnostics: routes, services, Twig, translations,
  config): `symfony lsp:check` — runs on the host, not in DDEV. Add `--source-only` to skip
  booting the app (what CI uses)
  - The Symfony CLI downloads, caches & checksums the Language Tools binary itself, outside
    the repo
  - `.symfony-lsp.json` points `phpCommand` at `ddev exec php` (with `containerProjectRoot: /var/www/html`),
    so runtime analysis boots the app in the container & doesn't depend on the host's PHP.
    Editors share this file with the checker

### Debugging & Discovery
When something fails, read today's `var/log/dev-YYYY-MM-DD.log` & the profiler (`/_profiler`, which includes GraphQL
requests) before changing code.

Framework APIs change between versions, so check the project rather than relying on memory:
- `ddev console about` – versions, environment, paths
- `ddev console debug:router`, `debug:container`, `debug:autowiring <name>`, `debug:config <bundle>`,
  `config:dump-reference <bundle>` – what exists & how it's configured
- Read the installed source & docblocks in `vendor/` (for `xm/symfony-bundle`, see its AGENTS.md)
- Docs: https://symfony.com/doc/current/

### Building Frontend Assets

Node & Yarn run in the DDEV web container (Node from `.nvmrc`, `nodejs_version: auto`) — use
`ddev yarn <cmd>`, not host `yarn`. `node_modules` is synced between the host & the container, so
`.yarnrc.yml` widens `supportedArchitectures` to cover both; without it the container dies on a
missing `@rolldown/binding-linux-*`. CI narrows it back to the runner's platform.

- Dev server with HMR: `ddev yarn dev` (run `ddev yarn install` first if packages changed); stop
  it with Ctrl-C. Assets are served through Apache at `/vite-dev/`, not the Vite port (see below)
  - If one's left running (`ddev yarn dev` fails on the port): `ddev exec pkill -f '[v]ite/bin/vite.js'`
- Compile check: `ddev yarn build:check` — use this to verify JS/CSS compiles. Builds to
  `node_modules/.build-check` (gitignored, and ignored by the dev server's watcher), so
  it's safe to run while the dev server is running. A green build only proves it bundles —
  the browser is still the real check
- Preview a production build: `ddev yarn preview` — serves `public/build` at
  `https://<site>.ddev.site:4173/build/`, mirroring the production paths
- Production build: `ddev yarn build` — production/deploy only. Never run it to verify a
  change: it empties and rewrites `public/build`, clobbering the manifest a running dev
  server relies on

#### Vite through Apache's proxy

Apache proxies `/vite-dev/` to the dev server in the same container (`.ddev/web-build/apache-vite.conf`,
copied into the image by `.ddev/web-build/Dockerfile`, so a change needs `ddev restart`). Dev assets are
same-origin with the site because browsers won't load an SVG `<use>` (the icon sprites) from another
origin, even with CORS. The dev `base` in `vite.config.mjs` must match the path — `vite-plugin-symfony`
writes it into `entrypoints.json`, so the bundle needs no separate config. The Vite port isn't exposed.

- **A `503` on a `/vite-dev/…` URL means the dev server isn't running** — start `ddev yarn dev`
- `server.origin`, `allowedHosts` & `server.hmr` are built from DDEV's `DDEV_PRIMARY_URL` (which
  includes the router's port when it isn't 443). `ProxyPreserveHost On` passes the site's host
  through, so it must be in `allowedHosts` — Vite rejects unknown hosts with a bare `400`
- The HMR socket rides the proxied path (`wss`, `upgrade=websocket` on the `ProxyPass`)
- The conf must be server-level: `ProxyPass` is inherited by vhosts, mod_rewrite directives
  aren't (a `RewriteRule … [P]` never fires)

### Package Management
- Add a capability with `ddev composer require <package>` & let its Flex recipe register the bundle
  & write its config — don't hand-edit `config/bundles.php` or hand-write a bundle's base config
- Install PHP packages: `ddev composer install` or `composer install`
- Install JS packages: `ddev yarn install`
- Upgrade all JS packages: `ddev yarn up -R "**"`
- Upgrade specific JS package: `ddev yarn up -R "package-name"`
- Upgrade JS packages (interactive for major versions): `ddev yarn upgrade-interactive`
- Upgrade, ignoring the age gate: `ddev yarn up:bypass <package>` — runs `yarn up -R` with
  `YARN_NPM_MINIMAL_AGE_GATE=0`, bypassing the 7 day `npmMinimalAgeGate` in `.yarnrc.yml`
  that otherwise skips just-published versions. Match every package with `"**"`, not
  `"*"` — `*` doesn't cross the `/` in scoped names, so it misses `@tailwindcss/*` etc.

### GraphQL Schema
- Generate GraphQL schema: `bin/generate_schema` (runs `ddev exec php bin/console graphql:dump-schema --with-descriptions --format=graphql`,
  with a Mutagen sync before & after so `schema.graphql` is on the host)

### Event Store & Projections
- Create event stream: `ddev console event-store:event-stream:create <stream_name>`
- Run projection once: `ddev console event-store:projection:run <projection_name> -o`
- Show all projection commands: `ddev console event-store:projection`

Existing event streams: `user`, `auth`
Existing projections: `user_projection`, `auth_projection`, `auth_log_projection`

### Database Migrations
- Generate an empty migration: `ddev console doctrine:migrations:generate` — not
  `make:migration`, which diffs against the entity mapping & would come back empty (`doctrine.yaml`
  sets `schema_filter` to ignore every table but `doctrine_migration_versions`)
- Run: `ddev console doctrine:migrations:migrate`
- Status: `ddev console doctrine:migrations:status`
- Never use `doctrine:schema:update` — schema changes go through projections or migrations

Migrations live in `src/Migrations/` under the `App\Migrations` namespace. They're services
(`enable_service_migrations`), so they're autowired — inject what they need (eg the kernel) through
the constructor or a `#[Required]` setter.

**Name them `VersionYYYYMMDD###`** — the date plus a 3 digit sequence for that day, eg
`Version20260826001`, `Version20260826002`. Doctrine generates `VersionYYYYMMDDHHMMSS`, so
rename the class & file after generating. Migrations run in string order, so keep the sequence
zero padded.

**A migration that creates, alters or drops a table must override `isTransactional()` to return
`false`.** MySQL implicitly commits on DDL, so Doctrine skips its commit & DBAL is left thinking a
transaction is still open. Anything later in the same `migrate` run that uses a transaction (eg a
projection run by another migration) then fails with "There is no active transaction" or
"SAVEPOINT DOCTRINE_2 does not exist". Data-only migrations should stay transactional. Migrations
using `ProjectionAwareMigration` are already non-transactional (the trait overrides it).

Most schema is created by projections (`src/Projection/*/*ReadModel.php`) or `db_create.sql`, so
migrations are for tables that aren't projection owned (eg `user_credential`, `user_token`) &
for one-off data changes.

### Makers
- Make aggregate root/model: `bin/console make:model` or `ddev console make:model` — also creates
  the model's projection, read model, finder & entity (there's no separate projection maker)
- Pass every argument up front & add `--no-interaction` — makers prompt by default, which hangs a
  non-interactive shell

### User Management
- Add a user: `bin/console app:user:add` or `ddev console app:user:add` (select `ROLE_SUPER_ADMIN` for admin access)

### Console commands
Write commands as invokable classes: `#[AsCommand]` on a class that doesn't extend `Command`, with
`__invoke()` taking `SymfonyStyle $io` & the input as `#[Argument]`/`#[Option]` parameters (the name
is the parameter in kebab-case; an option needs a default). Don't add `configure()` or name constants.
`CommandTester` takes the object directly.

### Command Log
`command_log` records every command payload & is only ever read for auditing, so it grows
without bound. Archive & prune it with `ddev console app:command-log:archive [before]`:

- `before` is a date (`YYYY-MM-DD`), required unless `--import` is used — everything sent before midnight UTC on it is
  archived. Only that exact format is accepted, so an interval like `"6 months"` can't be read
  as a future date & take the whole table
- It shows the date & asks for confirmation before archiving. Run it with `-n` to skip the
  prompt (for cron)
- Rows are written as `INSERT` statements to a gzipped SQL file in the current directory
  (`--path` to write elsewhere), then deleted — the file loads straight back into the table
- `--dry-run` counts what would be archived, `--keep` writes the file without deleting,
  `--batch-size` (default 1000) sets how many rows are read/written/deleted at a time
- Nothing is deleted until the archive is closed on disk
- Load an archive back with `--import=<file>` (gzipped or not). It runs in a transaction, so a
  failure part way through leaves the table untouched; `--dry-run` counts the statements

### Email templates
Emails are sent with Postmark templates through `EmailGatewayInterface` (`xm/symfony-bundle`).
Each template's alias is a case of the `App\Infrastructure\Email\EmailTemplate` enum, which is
passed straight to `send()`.

- To add an email, add a case to `EmailTemplate` & create the template in Postmark with that
  alias, on each Postmark server (local/dev, staging & production), before deploying
- `ddev console app:postmark:check-templates` checks the Postmark server for the current
  `POSTMARK_API_KEY` has an active template for every case & exits non-zero if not
- It runs as the last step of a deploy, after maintenance mode is turned off, so a missing
  template fails the job without holding back the release

### Maintenance mode
From `xm/symfony-bundle` (see its AGENTS.md), configured in `config/packages/xm_symfony.yaml`.
It's on while `var/maintenance` exists — `var/` is shared between releases, so no deploy needed:

- `ddev console app:maintenance on|off` (no argument for the status). `on` takes `--message`,
  `--until` (eg `"15:30"`, `"+30 minutes"`, in `user_time_zone`) & the options below; running
  it again updates it. It renders the page (overridden in `templates/bundles/XmSymfonyBundle/`)
  to `var/maintenance.html`; `off` deletes both
- Checked in `public/index.php` before the kernel's created, so it works even if the app won't
  boot. If the command won't run, a bare `touch var/maintenance` turns it on with a plain page
- Still let in, with a "Maintenance mode is on" notice (`base.html.twig`):
  - IPs/CIDR ranges: `--allow-ip`, `--remove-ip`, `--reset-ips`, `--allow-my-ip` (your SSH IP —
    your browser may use IPv6 instead). It's the connecting IP: the servers have `mod_remoteip`
  - Anyone with the key (new each time it's turned on, `--new-key` to replace): the command
    shows its URL. `?maintenance-key=…` on any URL sets a cookie until the browser's closed, or
    send `X-Maintenance-Key`. A deploy prints it to the job log
- Everyone else gets a 503 & the page, which reloads once it's over. In the Vue apps,
  `maintenanceLink` (`common/maintenance.js`) holds GraphQL requests & `common/maintenance.vue`
  shows a modal (checking every 2 minutes), then re-sends them. `session_expired.vue` pauses
  during it & re-checks the session after
- Messenger workers pause between messages until it's off
- Deploy with `MAINTENANCE=1` to turn it on from before the release switch until the migrations
  have run. It's then turned off, even if it was already on, & left on if the deploy fails. The
  up check accepts the maintenance 503
- Deploy with `SKIP_MIGRATIONS=1` to skip the migrations (so a long one doesn't use CI time), then
  run `doctrine:migrations:migrate` on the server. With `MAINTENANCE=1` too, maintenance mode is
  left on until you turn it off after the migrations
- In `test` the file is in the cache dir, so having it on locally doesn't fail the tests

### Sentry
Only enabled in `prod` & `staging` (backend `SentryBundle`, frontend `PROD` builds with a
`VITE_SENTRY_DSN`).

- Backend errors reach Sentry through Monolog (`Sentry\Monolog\Handler`), so the SDK's own error
  handlers are off. Don't add `FatalError` to `ignore_exceptions`: Monolog is the only way fatal
  errors get there
- `data_collection` (not `send_default_pii`) decides what request data is sent: the defaults
  include the body, so the GraphQL query, but filter values whose names look sensitive
  (password, token, key, session…) in headers, query params & JSON/form bodies. Cookies aren't
  sent: their values would be filtered & the CSRF cookie's name is its token. Check a
  new credential's name matches the list (`Sentry\DataCollection\KeyValueDataFilter`). Values in
  the URL path aren't filtered, so the bundle's `TokenScrubber` (`before_send`) replaces the
  `/activate/{token}`, `/verify/{token}` & `/recover/reset/{token}` tokens & the login link's `hash`
  everywhere in an event. Add any new token in a URL to `sentry_scrubber` in `xm_symfony.yaml` (see the
  bundle's AGENTS.md). The frontend never has these: each redirects before a page loads
- GraphQL errors also send the query text (`extra.query`, unfiltered), so pass secrets as
  variables, never inline in a query
- Frontend: `common/sentry.js`, called before any store is created (Pinia only applies plugins to
  stores created after them). `logError()` sends to Sentry, so use it for unexpected errors only.
  Component props aren't sent (`attachProps: false`), as they aren't filtered, & neither are
  failed `fetch()`s (`ignoreErrors`). JS/CSS files that fail to load are grouped into one
  `file-load-failure` issue as warnings: alert on its event count in Sentry, as a spike can mean
  a broken deploy
- The deploy sets the release (the commit SHA: `SENTRY_RELEASE` & `VITE_SENTRY_RELEASE`) & the
  frontend environment (`VITE_SENTRY_ENVIRONMENT`). Source maps are generated (`hidden`), uploaded
  & then deleted only when `SENTRY_AUTH_TOKEN` is set. It's a protected variable, so a staging
  deploy from an unprotected branch still reports errors, but without source maps

### DDEV Commands
- Start DDEV: `ddev start` (`ddev describe` for the URLs, including phpMyAdmin)
- Install PHP packages: `ddev composer install`
- Run Symfony console: `ddev console <command>`
- Import database: `ddev import-db --file=<file.sql[.gz]>` — replaces the database
- Run the Messenger worker: `ddev messenger` — sends what's routed to `async` (e.g.
  notification emails); runs in the foreground, stop it with Ctrl-C. It restarts when anything
  in `config/`, `src/`, `templates/` or `vendor/` changes (`symfony run --watch`)
- Xdebug: `ddev xdebug on` / `ddev xdebug off` (off by default)
- Moving a project from Lando: `/xm:lando-to-ddev`. Run it after pulling a project's switch to DDEV too:
  it moves your local databases from Lando into DDEV

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
- Examples: `UserProjection`, `AuthProjection`, `AuthLogProjection`
- Automatically run via `RunProjectionMiddleware` on event bus
- Each projection has: `*Projection.php`, `*ReadModel.php`, `*Finder.php`, `*Filters.php`, `*FilterQueryBuilder.php` (not every projection has all of these, e.g. `MessengerQueue` has only Finder/Filters/FilterQueryBuilder)
- Tables are created by `src/Projection/*/*ReadModel.php`; their names are constants in `src/Projection/Table.php`

**Process Managers** (`src/ProcessManager/`)
- React to events: coordinate cross-aggregate workflows, or carry out a side effect a projection can't
- Examples: `UserInviteProcessManager`, `UserInviteForMinimumProcessManager`, `ChangedPasswordProcessManager`, `UserUpdatedProfileProcessManager`, `UserDeletedProcessManager`
- Usually dispatch new commands, but don't have to — e.g. `UserDeletedProcessManager` only removes the `user_credential` row
- Every `*ProcessManager.php` in this directory is auto-tagged onto `messenger.bus.events` (`services.yaml`), so this is where anything that listens to a domain event goes

**Repositories** (`src/Infrastructure/Repository/`)
- Load and save aggregate roots to/from event streams
- Configured in `config/packages/event_sourcing.yaml`
- One per aggregate: `UserRepository`, `AuthRepository`

### GraphQL Layer

**Structure:**
- Schema types: `config/graphql/types/` (domain types in `domain/`, query/mutation configs as `*.query.yaml` / `*.mutation.yaml`)
- Queries: `src/GraphQl/Query/`
- Mutations: `src/GraphQl/Mutation/`
- Custom types: `src/GraphQl/Type/`
- Access control: inline expressions (e.g. `access: '@=hasRole("ROLE_ADMIN")'`) directly in the `*.query.yaml` / `*.mutation.yaml` files
  - `Query` & `Mutation` set `fieldsDefaultAccess` to `ROLE_ADMIN`, so a root field without `access` is admin only
    (it covers the decorator fields too). Public fields need `access: '@=true'` — keep `access` explicit on every
    root field. Don't set it on nested types: a denied field there nulls the parent object
- Error codes (Apollo style): errors carry a code in `extensions.code`, from `xm/symfony-bundle` (see its
  AGENTS.md). Its `GraphQlErrorSubscriber` (registered in `config/packages/graphql.yaml`) sets
  `UNAUTHENTICATED`/`FORBIDDEN` when access is denied (not signed in/signed in). For the others, throw the
  matching `CodedUserError` from `Xm\SymfonyBundle\Infrastructure\GraphQl\Error`
  (`throw new NotFoundError($message)`), or a project one from `src/GraphQl/Error/`. The frontend checks them
  with `getGraphQlErrorCode(e)` & `GraphQlErrorCodes` in `common/lib.js`; add new codes there too
- Passwords: mutations that set one throw `PasswordNotAllowedError` (`PASSWORD_NOT_ALLOWED`) when
  `Assert::passwordAllowed()` rejects it. The frontend runs the same checks, but its complexity score
  (zxcvbn-ts) can differ from the backend's (zxcvbn-php), so a password it accepts can still be rejected.
  The forms pass it to `useRejectedPasswords()` (`common/validation/user.js`) to show the error on the field

**Domain types** (`config/graphql/types/domain/`): `address.yaml`, `auth_log.yaml`, `file.yaml`, `messenger_queue_message.yaml`, `phone_number.yaml`, `upload.yaml`, `user.yaml`

**Frontend GraphQL:**
- Query/mutation files: `public/js/src/*/queries/*.graphql`
- Loaded via `@rollup/plugin-graphql`

### Doctrine Entities (Read Models Only)

Entities in `src/Entity/` are projection read models — never used for domain writes:
- `User`, `AuthLog`

The exceptions aren't projection owned (their tables are in `db_create.sql`):
- `UserCredential` holds the password hash (see [Password storage](#password-storage))
- `UserToken` holds the activation/verification/reset tokens

### Infrastructure Services (`src/Infrastructure/Service/`)

- `ChecksUniqueUsersEmailFromReadModel` - validates unique emails
- `UrlGenerator` - generates signed URLs
- `DefaultRouteProvider` - determines the default post-login route
- `UserPasswordStore` - reads/writes the password hash in `user_credential`
- `CommandLogArchiver` - archives `command_log` rows to a gzipped SQL file & imports them back

### Controllers (`src/Controller/`)

- `DefaultController` - main entry point
- `SecurityController` - authentication routes (login, logout)
- `SwitchUserRedirectController` - redirect handling for user impersonation (switch user)

Set a download's `Content-Disposition` with `ContentDisposition::make()` from `xm/symfony-bundle`
(`Xm\SymfonyBundle\Util`), not `HeaderUtils::makeDisposition()` – that throws on a filename with a
user's name in it, eg "Zoë" (see the bundle's AGENTS.md).

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

**Admin app sections** (`public/js/src/admin/`):
- `user/` - list, add, edit, view (requires `ROLE_ADMIN`)
- `admin_dashboard/` - dashboard
- `admin_delete/` - admin account deletion
- `auth_log/` - authentication log viewer
- `error/` - 403 & 404 pages
- `menu/` - admin navigation menu
- `messenger_queue/` - messenger queue viewer
- `pattern_library/` - UI pattern reference

**User app sections** (`public/js/src/user/`):
- `dashboard/` - user dashboard
- `error/` - 403 & 404 pages
- `login/` - login page
- `user_recover/` - initiate, reset (password recovery)
- `user_activate/` - account activation
- `user_verify/` - email verification
- `profile_edit/` - profile, password

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
- Constructor property promotion (PHP 8.0+), & `readonly` for VOs & DTOs
- Short array syntax `[]` not `array()`
- Trailing commas in multiline arrays, arguments, and parameters
- Ordered imports alphabetically
- Binary operator alignment for `=>` in arrays
- Use Doctrine attributes for entity mapping
- Don't use comments unless necessary; prefer self-explanatory code
- Use VOs to encapsulate primitive types & pass domain concepts
- Don't call `->toString()` on objects when the receiver already converts them to a string
  (`sprintf()`, interpolation, concatenation). Keep it only where a real `string` is required
  (array keys, strict `string` params, strict comparisons)
- Use FakerPHP to generate test data, including the UuidFakerProvider
- Use `Carbon\CarbonImmutable` instead of `\DateTimeImmutable` when creating dates/times
  (it extends `\DateTimeImmutable`, so it satisfies existing type hints). Keep `\DateTimeImmutable`
  in type hints/return types & Doctrine mappings, since values from Doctrine, Symfony & Prooph
  won't be Carbon instances; convert with `CarbonImmutable::instance()` when needed.
- Never write a UTC offset into code or tests — convert through the zone. Zone rules change (eg a
  region dropping daylight saving), so an offset that's right today can be wrong later
- Keep line length to 120 characters unless it makes code less readable

Run `ddev composer cs:fix` to auto-fix most style issues.

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
- Max 5 attributes per line for single-line tags
- 1 attribute per line for multiline (first attribute on same line allowed)
- Component self-closing: always for components, optional for HTML elements
- No newline before closing bracket in multiline tags
- Component names in templates: PascalCase (`<RouterLink>`, `<FieldInput>`)

Run `yarn lint:js:fix` and `yarn lint:css:fix` to auto-fix style issues.

## Important Notes

- **Never modify aggregates directly** - always use commands/handlers
- **Projections are eventually consistent** - run projection commands after event changes
- **Don't bypass event sourcing** - read from read models, write via commands
- **Test coverage required** - especially for aggregates and handlers
- **Use type hints** - strict types are declared in all PHP files
- **Memory**: Some operations (tests, projections) may need `php -d memory_limit=-1`
- **MySQL**: Add indexes within the create statement. Name them with the column name (or the columns, for a
  composite index, e.g. `user_date`).

### Password storage

The password hash is authentication state, not domain history, so it's deliberately kept out
of the event stream & out of command payloads — otherwise every hash a user ever had would be
kept forever in an append-only store (& a second copy in `command_log`, which records every
command payload).

- Stored in its own `user_credential` table (`db_create.sql`), written by
  `UserPasswordStore` & read through the `User` → `UserCredential` association
- **Not projection owned**: a projection reset or replay doesn't touch it & can't restore it.
  `user_credential` is the authority for the hash
- `ChangedPassword`, `AdminChangedPassword` & `PasswordUpgraded` carry no payload — they're
  the fact that the password changed, which is all the notification process manager & audit
  trail need
- Callers write the hash & dispatch the command separately. On the add paths the store is
  written **after** the command so a rejected command doesn't leave a stray credential; on
  the change paths it's written **before**
- `UserDeletedProcessManager` removes the row on `UserWasDeletedByAdmin`, since the user
  projection can't
- `User::password()`/`getPassword()` return `null` when there's no credential row, which
  means the user can't log in

### Session expiry

Sessions expire after `framework.session.gc_maxlifetime` of inactivity, enforced by
`xm/symfony-bundle` (`SessionExpiry` & `SessionExpirySubscriber`, on by default — PHP's GC
alone doesn't: it doesn't check a session's age when reading it). Remember-me sign ins don't
expire; the bundle reads the cookie names from the firewall config.

- Every request by a signed in user extends the session, except routes with
  `SessionExpiry::EXTEND_ATTRIBUTE` (`_extend_session`) set to `false` in their defaults
- `/session-info` is one: a `GET` returns the user ID & seconds remaining without extending
  it, a `POST` extends it
- `common/session_expired.vue` checks it, warns 2 minutes before with a countdown ("Keep me
  signed in"), then shows the signed out modal
- GraphQL requests rejected because the user's signed out are held by `sessionLink`
  (`common/session.js`) & re-sent once they sign back in, instead of the component showing an error

### Login throttling

Symfony's `login_throttling` on the `main` firewall (`security.yaml`, needs `symfony/rate-limiter`):
10 failed attempts per email + IP per 15 minutes, & 50 per IP across all emails. It applies to every
authenticator on the firewall (password & login link). A successful login resets the email + IP limit only.

- The limiter state is in `cache.rate_limiter` (on `cache.app`, the filesystem)
- It's keyed on the client IP, so it depends on `framework.trusted_proxies` — without it every
  request behind the proxy shares one IP
- The error ("Too many failed login attempts, please try again in X minutes.") reaches the login
  page through `AuthLast`, like any other auth error

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
- Symfony 8.1 on PHP 8.5
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
- DDEV (Docker-based local dev environment)
- Node 26 with Yarn v4
- MySQL 8.4

## Browser Automation

Use `agent-browser` for web automation. Run `agent-browser --help` for all commands.

Core workflow:
1. `agent-browser open <url>` - Navigate to page
2. `agent-browser snapshot -i` - Get interactive elements with refs (@e1, @e2)
3. `agent-browser click @e1` / `fill @e2 "text"` - Interact using refs
4. Re-snapshot after page changes

## Configuration Notes

- Environment config: Copy `.env.local-template` to `.env.local` and update `@todo-symfony` values
- `.env` is committed & holds defaults only — secrets go in `.env.local` (git-ignored), never `.env`
- DDEV project name is in `.ddev/config.yaml` (default: `symfonystarter`, so https://symfonystarter.ddev.site)
- `DATABASE_URL` is set by DDEV (`web_environment` in `.ddev/config.yaml`), so it overrides the `.env` files
- Vite uses its default ports in every project: 5173 for the dev server (in `.ddev/web-build/apache-vite.conf`; not exposed — proxied at `/vite-dev/`) & 4173 for the preview (in `.ddev/config.yaml`). Ports are per container & DDEV's router routes by hostname, so they don't clash between projects
- `ddev restart` applies changes to `.ddev/config.yaml`
- Local-only DDEV overrides (eg `router_https_port` while another tool holds 443) go in the git-ignored
  `.ddev/config.local.yaml`
- Database collation: tables, IDs & codes use `utf8mb4_0900_bin`; human-readable text (names, emails,
  messages) uses `utf8mb4_0900_ai_ci`. Both are `NO PAD`, so trailing spaces count in comparisons &
  unique indexes – trim values before saving. Columns compared or joined across tables must use the
  same collation, or MySQL throws "Illegal mix of collations"

### Environments

`dev` (local, DDEV), `test`, `staging` (the site customers preview & test on) & `prod` (live).
`staging` is customer-facing, so it's configured to behave like `prod`, not like `dev`:

- `.env.staging` sets `APP_DEBUG=0` — Symfony treats every env except `prod` as a debug env by
  default (`Dotenv::bootEnv()`)
- The `when@prod` blocks in `config/packages/` are shared with `staging` via a YAML anchor
  (`when@prod: &prod` / `when@staging: *prod`), so the two can't drift. **When adding a
  `when@prod` block, decide whether `staging` needs it too & alias it rather than copying.**
  Currently aliased: `monolog.yaml`, `sentry.yaml`, `doctrine.yaml`, `routing.yaml`
- `config/bundles.php` enables `SentryBundle` for `prod` & `staging`
- `framework.disallow_search_engine_index` is on for `staging` only
- `deploy to staging` in `.gitlab-ci.yml` installs & builds like `deploy to prod`
- Staging-only config goes in its own `when@staging` block below the alias
- Verify a config change against staging with `APP_ENV=staging bin/console lint:container`
- `framework.trusted_proxies` applies in **all** envs (`%env(TRUSTED_PROXIES)%`), not just `dev`:
  deployed sites sit behind a proxy/CDN. `.env` defaults it to empty (trust nothing); each env
  sets it in its own `.env.local` (`shared/.env.local` on the servers). `Request::setTrustedProxies()`
  accepts the `REMOTE_ADDR` & `private_ranges` tokens as well as IPs/CIDRs. An empty value is
  falsy in `Kernel::initializeContainer()`, so nothing is trusted — it does not become `['']`

### Provisioning Scripts

Run once when a project's server is set up, never against the starter (the README has the runbook):
- `bin/provision_site` — orchestrates provisioning a new site on an InterWorx server
- `bin/setup_gitlab_ci_vars` — sets the GitLab CI/CD variables for one scope
- `bin/setup_staging.sh` & `bin/setup_prod.sh` — create the release/shared structure; run on the server

Never run these — they're interactive and make changes to servers, 1Password and GitLab.
`provision_site` and `setup_gitlab_ci_vars` share the same structure, helpers and output style
with the Craft starter's; keep them in sync when editing either one.

## Code Intelligence

Prefer LSP over Grep/Glob/Read for code navigation:
- `goToDefinition` / `goToImplementation` to jump to source
- `findReferences` to see all usages across the codebase
- `workspaceSymbol` to find where something is defined
- `documentSymbol` to list all symbols in a file
- `hover` for type info without reading the file
- `incomingCalls` / `outgoingCalls` for call hierarchy

Before renaming or changing a function signature, use
`findReferences` to find all call sites first.

Use Grep/Glob only for text/pattern searches (comments,
strings, config values) where LSP doesn't help.

After writing or editing code, check LSP diagnostics before
moving on. Fix any type errors or missing imports immediately.

## Maintenance

### Updating Recipes
The `symfony/framework-bundle` recipe copies its own generic `AGENTS.md` into the project, so
`composer recipes:update symfony/framework-bundle` replaces this file. Restore ours after (keep
anything useful from the recipe's version by merging it in by hand).

### Updating PHP Version
When upgrading PHP, update version in these files:
- `composer.json` - add polyfill for new version
- `.ddev/config.yaml`
- `bin/setup_staging.sh`, `bin/setup_prod.sh`, `.gitlab-ci.yml`, `.github/workflows/ci.yml`
- `.php-cs-fixer.dist.php`

Then run: `ddev restart && ddev composer update && bin/check_full`

Write conditionals in yoda style.
