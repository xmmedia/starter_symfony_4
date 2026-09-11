# AGENTS.md

This file provides guidance to agents when working with code in this repository.

## Auto-generated Files — Do Not Edit

- `schema.graphql` — auto-generated from YAML config in `config/graphql/types/`. Never edit this file directly.
- `config/reference.php` — auto-generated. Never edit this file directly.

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
- Avoid named arguments unless they make the call clearer — skipping over a run of optional
  parameters, or disambiguating consecutive booleans. Otherwise pass positionally.
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
- Run all checks: `bin/check_full` (runs rector, php-cs-fixer, bin/generate_schema, then bin/check)
- PHP CS (dry run): `lando composer cs` or `composer cs`
- PHP CS (fix): `lando composer cs:fix` or `composer cs:fix`
- PHPStan static analysis: `lando composer static` or `composer static`
- Rector (dry run): `lando composer rector` or `composer rector`
- Rector (fix): `lando composer rector:fix` or `composer rector:fix`
- Lint JS: `lando yarn lint:js` or `lando yarn lint:js:fix`
- Lint CSS: `lando yarn lint:css` or `lando yarn lint:css:fix`
- Lint YAML: `lando console lint:yaml config`
- Lint Twig: `lando console lint:twig templates`
- Lint container: `lando console lint:container`
- Symfony Language Tools (Symfony-aware diagnostics: routes, services, Twig, translations,
  config): `symfony lsp:check` — runs on the host, not in Lando. Add `--source-only` to skip
  booting the app (what CI uses)
  - The Symfony CLI downloads, caches & checksums the Language Tools binary itself, outside
    the repo
  - `.symfony-lsp.json` points `phpCommand` at `lando php` (with `containerProjectRoot: /app`),
    so runtime analysis boots the app in the container & doesn't depend on the host's PHP.
    Editors share this file with the checker

### Building Frontend Assets

Node & Yarn run in the Lando `node` service — use `lando yarn <cmd>`, not host `yarn`, so the
Node version matches `.nvmrc`. `node_modules` is on the shared mount, so `.yarnrc.yml` widens
`supportedArchitectures` to cover host & container; without it the container dies on a missing
`@rolldown/binding-linux-*`. CI narrows it back to the runner's platform.

- Dev server with HMR: `lando vite` — runs `yarn install`, then `yarn dev`, in the container.
  Assets are served through the appserver at `/vite-dev/`, not the Vite port (see below)
  - Stop it: `lando vite-stop` — killing `lando vite` on the host can leave the process
    holding the port inside the container
  - HTTPS: the cert Lando issues the service in the container, `vite-plugin-mkcert` on the
    host. `vite.config.mjs` picks by checking for `/certs/cert.crt` — mkcert can't run in the
    container (needs root, & its CA wouldn't be trusted by the host browser)
  - To run it on the host instead: `yarn dev`, no config change — Apache falls back to it
- Compile check: `lando yarn build:check` — use this to verify JS/CSS compiles. Builds to
  `node_modules/.build-check` (gitignored, and ignored by the dev server's watcher), so
  it's safe to run while `lando vite` is running. A green build only proves it bundles —
  the browser is still the real check
- Preview a production build: `lando yarn preview` — serves `public/build` at
  `https://localhost:9508/build/`, mirroring the production paths (`base` keys off `isPreview`
  as well as `command`). 9508 is the only port the `node` service publishes
- Production build: `lando yarn build` — production/deploy only. Never run it to verify a
  change: it empties and rewrites `public/build`, clobbering the manifest a running
  `lando vite` relies on

#### Vite through the appserver proxy

Apache proxies `/vite-dev/` to the node service (`lando_apache_vite.conf`, symlinked into
`conf-enabled` by a `build_as_root` step) so dev assets are same-origin with the site. `base`
in `vite.config.mjs` must match that path — `vite-plugin-symfony` writes it into
`entrypoints.json`, so the bundle needs no separate config. The Vite port isn't published:
the appserver reaches it internally, and publishing it would shadow a host-run `yarn dev`.

- **A `503` on a `/vite-dev/…` URL means the dev server isn't running** — start `lando vite`.
  `pgrep -f vite` in the container false-positives on its own `sh -c` wrapper; check for a
  listener on the port instead
- `ProxyPreserveHost On` passes the site's host through, so it must be in `allowedHosts` —
  Vite rejects unknown hosts with a bare `400` that looks like a proxy bug
- `server.hmr` uses `wss` on `clientPort` 443: the HMR socket rides the proxied path
- The conf must be server-level: `ProxyPass` is inherited by vhosts, mod_rewrite directives
  aren't (a `RewriteRule … [P]` never fires). Hence `upgrade=websocket` on the members
- The balancer keeps a host-run `yarn dev` (`host.docker.internal`) as hot standby. Editing a
  `BalancerMember` needs a full Apache restart — a graceful reload silently keeps the old values

### Package Management
- Install PHP packages: `lando composer install` or `composer install`
- Install JS packages: `lando yarn install`
- Upgrade all JS packages: `lando yarn up -R "**"`
- Upgrade specific JS package: `lando yarn up -R "package-name"`
- Upgrade JS packages (interactive for major versions): `lando yarn upgrade-interactive`
- Upgrade, ignoring the age gate: `lando yarn up:bypass <package>` — runs `yarn up -R` with
  `YARN_NPM_MINIMAL_AGE_GATE=0`, bypassing the 7 day `npmMinimalAgeGate` in `.yarnrc.yml`
  that otherwise skips just-published versions. Match every package with `"**"`, not
  `"*"` — `*` doesn't cross the `/` in scoped names, so it misses `@tailwindcss/*` etc.

### GraphQL Schema
- Generate GraphQL schema: `bin/generate_schema` (runs `lando console graphql:dump-schema --with-descriptions --format=graphql`)
- Access GraphiQL (dev only): `/graphiql`

### Event Store & Projections
- Create event stream: `lando console event-store:event-stream:create <stream_name>`
- Run projection once: `lando console event-store:projection:run <projection_name> -o`
- Show all projection commands: `lando console event-store:projection`

Existing event streams: `user`, `auth`
Existing projections: `user_projection`, `auth_projection`, `auth_log_projection`

### Database Migrations
- Generate an empty migration: `lando console doctrine:migrations:generate` — not
  `make:migration`, which diffs against the entity mapping & would come back empty (`doctrine.yaml`
  sets `schema_filter` to ignore every table but `doctrine_migration_versions`)
- Run: `lando console doctrine:migrations:migrate`
- Status: `lando console doctrine:migrations:status`

Migrations live in `migrations/` under the `DoctrineMigrations` namespace (they're deliberately
not autoloaded — see `config/packages/doctrine_migrations.yaml`).

**Name them `VersionYYYYMMDD###`** — the date plus a 3 digit sequence for that day, eg
`Version20260826001`, `Version20260826002`. Doctrine generates `VersionYYYYMMDDHHMMSS`, so
rename the class & file after generating. Migrations run in string order, so keep the sequence
zero padded.

Most schema is created by projections (`src/Projection/*/ReadModel.php`) or `db_create.sql`, so
migrations are for tables that aren't projection owned (eg `user_credential`, `user_token`) &
for one-off data changes.

### Makers
- Make aggregate root/model: `bin/console make:model` or `lando console make:model`
- Make projection: `bin/console make:projection` or `lando console make:projection`

### User Management
- Add a user: `bin/console app:user:add` or `lando console app:user:add` (select `ROLE_SUPER_ADMIN` for admin access)

### Command Log
`command_log` records every command payload & is only ever read for auditing, so it grows
without bound. Archive & prune it with `lando console app:command-log:archive [before]`:

- `before` is a required date (`YYYY-MM-DD`) — everything sent before midnight UTC on it is
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

### Maintenance mode
From `xm/symfony-bundle` (see its AGENTS.md), configured in `config/packages/xm_symfony.yaml`.
It's on while `var/maintenance` exists — `var/` is shared between releases, so no deploy needed:

- `lando console app:maintenance on|off` (no argument for the status). `on` takes `--message`,
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
- In `test` the file is in the cache dir, so having it on locally doesn't fail the tests

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
- Examples: `UserProjection`, `AuthProjection`, `AuthLogProjection`
- Automatically run via `RunProjectionMiddleware` on event bus
- Each projection has: `*Projection.php`, `*ReadModel.php`, `*Finder.php`, `*Filters.php`, `*FilterQueryBuilder.php` (not every projection has all of these, e.g. `MessengerQueue` has only Finder/Filters/FilterQueryBuilder)
- Create tables defined in `src/Projection/Table.php` and `src/Projection/*/ReadModel.php`

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
- Error codes (Apollo style): errors carry a code in `extensions.code`, from `xm/symfony-bundle` (see its
  AGENTS.md). Its `GraphQlErrorSubscriber` (registered in `config/packages/graphql.yaml`) sets
  `UNAUTHENTICATED`/`FORBIDDEN` when access is denied (not signed in/signed in). For the others, throw the
  matching `CodedUserError` from `Xm\SymfonyBundle\Infrastructure\GraphQl\Error`
  (`throw new NotFoundError($message)`). The frontend checks them with `getGraphQlErrorCode(e)` &
  `GraphQlErrorCodes` in `common/lib.js`; add new codes there too

**Domain types** (`config/graphql/types/domain/`): `address.yaml`, `auth_log.yaml`, `file.yaml`, `messenger_queue_message.yaml`, `phone_number.yaml`, `upload.yaml`, `user.yaml`

**Frontend GraphQL:**
- Query/mutation files: `public/js/src/*/queries/*.graphql`
- Loaded via `@rollup/plugin-graphql`

### Doctrine Entities (Read Models Only)

Entities in `src/Entity/` are projection read models — never used for domain writes:
- `User`, `UserToken`, `AuthLog`

`UserCredential` is the exception: it holds the password hash & is **not** projection owned
(see [Password storage](#password-storage)).

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
- `menu/` - admin navigation menu
- `messenger_queue/` - messenger queue viewer
- `pattern_library/` - UI pattern reference

**User app sections** (`public/js/src/user/`):
- `dashboard/` - user dashboard
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
- Constructor property promotion (PHP 8.0+)
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
- **Memory**: Some operations (tests, projections) may need `php -d memory_limit=-1`
- **MySQL**: Add indexes within the create statement. Name them with the column name.

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
- Symfony 7.4 on PHP 8.5
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
- Node 24 with Yarn v4
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
- Lando site name is in `.lando.yml` (default: `symfonystarter`)
- Vite dev server port in `vite.config.mjs` & `lando_apache_vite.conf` (currently: 9008, unpublished — proxied at `/vite-dev/`)
- Vite preview port in `vite.config.mjs` & `.lando.yml` (currently: 9508)
- `lando rebuild` (not `lando start`) is needed after changes under `config:` or a service's
  `ports:`; it recreates the appserver too, so run `lando start` after or the app 404s
- Database collation should be `utf8mb4_bin`

### Environments

`dev` (local, Lando), `test`, `staging` (the site customers preview & test on) & `prod` (live).
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

### Updating PHP Version
When upgrading PHP, update version in these files:
- `composer.json` - add polyfill for new version
- `.lando.yml`
- `setup_staging.sh`, `setup_prod.sh`, `.gitlab-ci.yml`, `.github/workflows/ci.yml`
- `.php-cs-fixer.dist.php`

Then run: `lando rebuild && lando composer update && bin/check_full`

Write conditionals in yoda style.
