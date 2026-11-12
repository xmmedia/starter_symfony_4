# Symfony Starter

Used to create new projects using [Symfony 7](https://symfony.com/) at [XM Media](https://www.xmmedia.com/).

Local/Dev: https://symfonystarter.ddev.site @todo-symfony  
Staging: @todo-symfony  
Production: @todo-symfony

## Setting Up a New Site

_Note:_ Make sure your git configuration is set to use the correct line endings: `git config --global core.autocrlf input && git config --global core.eol lf`

1. Create a new project:
    ```sh
    composer create-project xm/starter_symfony_4 project-name --stability=dev --no-install --remove-vcs
    ```
   To run this, Composer needs to be installed globally. If you don't have Composer installed, follow the instructions at [getcomposer.org/download](https://getcomposer.org/download/).
1. Copy `.env.local-template` to `.env.local` and update the values where there are `@todo-symfony` comments.
1. Update `composer.json`: `name`, `license` (likely `proprietary`) and `description`
1. Update `package.json`: `name`, `version`, `git.url`, `license` (probably delete), `private`
1. Update the project `name` in `.ddev/config.yaml` (the local site is `https://<name>.ddev.site`)
1. Remove or update the `LICENSE` file.
1. Run/Start the DDEV site: `ddev start`
1. Composer install & update: `ddev composer install && ddev composer update` (or without DDEV or without memory limit: `php -d memory_limit=-1 /usr/local/bin/composer update`)
1. Run `ddev yarn install && ddev yarn up -R "**"`.
1. Run `ddev yarn dev` (dev) or `ddev yarn build` (production) to compile JS & CSS files.
1. Give executable perms to bin dir: `chmod u+x bin/*` (helpful, but optional)
1. Create database with event streams & projections tables from `db_create.sql` using `ddev import-db --file=db_create.sql`. 
    - If possible, set database collation to `utf8mb4_0900_bin`: `ALTER DATABASE <database_name> CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_bin;`
1. Create the required event streams with the command: `bin/console event-store:event-stream:create user && bin/console event-store:event-stream:create auth` (or if using DDEV: `ddev console event-store:event-stream:create user && ddev console event-store:event-stream:create auth`).
1. Run all projections once: `bin/console app:projection:run user -o && bin/console app:projection:run auth -o && bin/console app:projection:run auth_log -o` (or if using DDEV: `ddev console app:projection:run user -o && ddev console app:projection:run auth -o && ddev console app:projection:run auth_log -o`).
1. Create a user `bin/console app:user:add` (select role `ROLE_SUPER_ADMIN`) (or if using DDEV: `ddev console app:user:add`).
1. Find and make changes near `@todo-symfony` comments throughout the site.
1. Delete starter files: `README.md` (or update) and `TEMPLATES.md`.
1. *Optional:* Run `composer test` – will install PHPUnit & run PHP tests
1. Create new favicons: [realfavicongenerator.net](https://realfavicongenerator.net)
1. Copy (use "Push to another server") or recreate the templates in Postmark. The templates are referenced by the aliases.
1. *Optional:* Run `bin/console app:graphql:dump-schema <username>` to update the GraphQL schema file where `username` is the email of an admin user.
1. *Optional:* Rename the project in PhpStorm.
1. Update the Sentry `org` & `project` in `vite.config.mjs`. Source maps are uploaded by the deploy when the `SENTRY_AUTH_TOKEN` CI variable is set (`bin/setup_gitlab_ci_vars` asks for it).

**Local dev site can be accessed at: https://[domain]/**

## Setting Up Starter

1. Checkout the repo.
1. Copy `.env.local-template` to `.env.local` and update the values where there are `@todo-symfony` comments.
1. Run/Start the DDEV site: `ddev start`
1. Composer install: `ddev composer install` or `composer install` to run without DDEV.
1. Run `ddev yarn install`.
1. Run `ddev yarn dev` (dev) or `ddev yarn build` (production) to compile JS & CSS files.
1. Give executable perms to bin dir: `chmod u+x bin/*` (helpful, but optional)
1. Create database with event streams & projections tables from `db_create.sql` using `ddev import-db --file=db_create.sql`. 
    - If possible, set database collation to `utf8mb4_0900_bin`: `ALTER DATABASE <database_name> CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_bin;` This can be done through PhpMyAdmin (URL shown by `ddev describe`)
1. Create the required event streams with the command: `bin/console event-store:event-stream:create user && bin/console event-store:event-stream:create auth` (or if using DDEV: `ddev console event-store:event-stream:create user && ddev console event-store:event-stream:create auth`).
1. Run all projections once: `bin/console app:projection:run user -o && bin/console app:projection:run auth -o && bin/console app:projection:run auth_log -o` (or if using DDEV: `ddev console app:projection:run user -o && ddev console app:projection:run auth -o && ddev console app:projection:run auth_log -o`).
1. Create a user `bin/console app:user:add` (select role `ROLE_SUPER_ADMIN`) (or if using DDEV: `ddev console app:user:add`).
1. *Optional:* Run `composer test` – will install PHPUnit & run PHP tests – or `ddev composer test` if using DDEV.
1. Run `bin/check` to run all code tests/checks.

**Local dev site can be accessed at: https://symfonystarter.ddev.site

## Setting Up a Server

`bin/setup_staging.sh` & `bin/setup_prod.sh` run on the server. The other setup scripts run locally.

For sites on an InterWorx server, `bin/provision_site` (run locally) does the whole setup:
1Password items, the SiteWorx account, the database, `setup_staging.sh`/`setup_prod.sh`, `shared/.env.local`
(the repo's `.env` with the environment, app secret, database & request context filled in), the Messenger worker
& log rotation cron jobs, the GitLab CI/CD variables and the CI public key in the site user's `~/.ssh/authorized_keys`.

  - Requires an ssh Host with passwordless sudo, [`op`](https://developer.1password.com/docs/cli/)
    signed in, [`glab`](https://gitlab.com/gitlab-org/cli) authenticated and `jq` installed.
  - It asks whether you're provisioning staging or production, then prompts for everything
    else and shows a summary before making any changes.
  - The remaining manual steps (DNS record, SSL certificate, completing `shared/.env.local`, first deploy,
    database) are printed at the end. The DNS record doesn't need to exist beforehand —
    it's only needed before generating the SSL certificate.

To do it by hand instead:

1. Upload `bin/setup_staging.sh` (or `bin/setup_prod.sh`) to the domain dir, where the `html` dir is, and run
   it: `bash ./setup_staging.sh`. It creates the `releases`/`shared` structure & the links
   (`html` → `current/public`), switches PHP to the minimum version if it's older and installs oh-my-zsh (plus nvm
   & Composer on staging).
   - The staging script can be run more than once. The prod script stops if the `releases` dir already exists.
1. Fill in `shared/.env.local` on the server. The script creates it empty, and each deploy copies it into the release.
1. Locally, run `bin/setup_gitlab_ci_vars <group/project>` once per scope (staging, production) to set the GitLab
   CI/CD variables. It needs `glab` (authenticated) & `jq`. If it generates an SSH key, add the public key it prints
   to the server's `~/.ssh/authorized_keys`. See `--help` for the options that pre-fill its prompts.
1. Run the deploy job in GitLab.

### Cron Jobs

`bin/provision_site` adds the Messenger worker & log rotation jobs. To add them by hand, or any others, add them in
SiteWorx (Cron), replacing `[site_base]` with the domain dir, where `current` is:

  - Start a Messenger worker every 5 minutes, unless one's running (`flock`). The worker exits after an hour, or when
    a deploy runs `messenger:stop-workers`, & the next run starts a new one:
    ```
    */5 * * * * cd [site_base]/current && flock -n var/messenger.lock bin/console messenger:consume async --time-limit=3600 --memory-limit=128M --quiet 2>&1
    ```
  - Rotate the logs in `var/log/` (`config/packages/logrotate.conf`), every Monday at 3am. `var/` is shared between
    releases, so the state file is kept across deploys:
    ```
    0 3 * * 1 cd [site_base]/current && /usr/sbin/logrotate config/packages/logrotate.conf --state var/logrotate-state
    ```

## Environments

  - `dev` – local development, via DDEV
  - `test` – automated tests
  - `staging` – the site customers preview & test on, set up with `bin/setup_staging.sh`
  - `prod` – the live site, set up with `bin/setup_prod.sh`

`staging` is a customer-facing site, so it's configured to behave like `prod`, not like `dev`:

  - `.env.staging` sets `APP_DEBUG=0`. Symfony treats every env except `prod` as a debug env by
    default (`Dotenv::bootEnv()`), which would expose stack traces & slow the site down
  - The `when@prod` blocks in `config/packages/` are shared with `staging` through a YAML anchor
    (`when@prod: &prod` / `when@staging: *prod`), so the two can't drift apart. This covers
    monolog handlers, Sentry, the doctrine query/result caches & the router's `strict_requirements`
  - `SentryBundle` is enabled for both `prod` & `staging` in `config/bundles.php`
  - `framework.disallow_search_engine_index` is on for `staging` so the preview site isn't indexed
  - The GitLab `deploy to staging` job installs & builds the same way `deploy to prod` does
    (`composer install --no-dev`, `yarn build`)

Anything genuinely staging-only belongs in a `when@staging` block of its own, below the alias.

`TRUSTED_PROXIES` must be set per environment, in that environment's own `.env.local`
(on the servers: `shared/.env.local`). It applies in every env, not just `dev` — deployed sites
sit behind a proxy/CDN, and without it the client IP recorded in the auth log & the scheme used
for cookie security come from the proxy rather than the real client. It defaults to empty (trust
nothing). Set it no wider than the proxies actually in front of the app:

  - behind Cloudflare only – the [Cloudflare IP ranges](https://www.cloudflare.com/ips/)
  - behind a local reverse proxy – `private_ranges`
  - a single proxy on the same box (DDEV's router does this) – `127.0.0.1,REMOTE_ADDR`

## System Requirements

### Server

  - PHP 8.5
  - MySQL 8.4

### Locally for Development

  - [DDEV](https://ddev.com/) – the web container runs [Node 26](https://nodejs.org/) & [Yarn v4](https://yarnpkg.com/), so neither is needed on the host

Run yarn through `ddev yarn` so the Node version matches `.nvmrc`. `node_modules` is synced
between the host & the container, so one `ddev yarn install` serves both. Host `yarn` still works,
but the Node version is then up to you.

## Commands

  - Run all checks: `bin/check_full`
    - Note: this generates the GQL schema first, because within the checks we validate the graphql operations files
  - Check all code: `bin/check`
  - Generate GQL schema: `bin/generate_schema`
  - Dev JS/CSS server with HMR: `ddev yarn dev` (stop with Ctrl-C)
    - Stop one left running in the container: `ddev exec pkill -f '[v]ite/bin/vite.js'`
    - Assets are proxied through Apache at `https://[domain]/vite-dev/`, same-origin with the site (needed for the SVG icon sprites) — the Vite port isn't exposed. See `.ddev/web-build/apache-vite.conf`
    - A `503` on a `/vite-dev/…` asset means the dev server isn't running
  - Compile check: `ddev yarn build:check`
    - Builds to `node_modules/.build-check`, so it's safe to run while the dev server is running
  - Production JS/CSS build: `ddev yarn build`
    - Don't use this to verify a change — it rewrites `public/build`, clobbering the manifest a running dev server relies on
  - Preview a production build: `ddev yarn preview`
    - Serves `public/build` at `https://[domain]:4173/build/`, mirroring the production paths
  - JS Tests ([Vitest](https://vitest.dev/)): `ddev yarn test:unit`
  - Linting:
    - JS ([ESLint](https://eslint.org/)): `ddev yarn lint:js` or `ddev yarn lint:js:fix`
    - CSS: `ddev yarn lint:css` or `ddev yarn lint:css:fix`
  - Messenger worker: `ddev messenger` – processes the `async` queue (e.g. notification emails) until stopped with Ctrl-C, restarting on code changes
  - Install PHP packages: `ddev composer install` or `composer install`
  - Install JS packages: `ddev yarn install` — also the fix for `Couldn't find the node_modules state file`
  - PHP Tests ([PhpUnit](https://phpunit.de/)): 
    - `ddev composer test` or `composer test`
    - no memory limit `php -d memory_limit=-1 bin/phpunit`
    - with coverage (HTML) `composer test:coverage`
    - show deprecations `composer test:deprecations`
  - [PHP CS](https://cs.sensiolabs.org/):
    - Dry run: `ddev composer cs` or `composer cs`
    - Fix: `ddev composer cs:fix` or `composer cs:fix`
  - PHP Static Analysis ([PHPStan](https://github.com/phpstan/phpstan)): `ddev composer static` or `composer static`
  - Symfony diagnostics ([Symfony Language Tools](https://github.com/symfony/language-tools)): `symfony lsp:check`
    - Checks routes, services, Twig, translations & bundle config; runs on the host, not in DDEV
    - `--source-only` skips booting the app (what CI uses)
    - `.symfony-lsp.json` runs the app through `ddev exec php` (`ddev php` would sync Mutagen on every call), so runtime analysis doesn't depend on the host's PHP version — editors share this config too
    - The [Symfony CLI](https://symfony.com/download) downloads & caches the Language Tools binary itself, outside the repo
  - Projections:
    - Show all commands: `bin/console event-store:projection`
    - Run once: `bin/console event-store:projection:run user_projection -o`
  - Makers (PHP):
    - Make aggregate root/model (including its projection): `bin/console make:model`
  - Upgrade JS packages: `ddev yarn up -R "**"`
    - Upgrade a specific package: `ddev yarn up -R "package-name"`
    - Upgrade major versions: `ddev yarn upgrade-interactive` (ctrl+c to exit without changes)
    - Upgrade a package, ignoring the age gate: `ddev yarn up:bypass <package>`

## Incorporated Libraries & Tools

  - [DDEV](https://ddev.com/) – local dev environment
  - Frontend – full list of dependencies can be found in [package.json](https://github.com/xmmedia/starter_symfony_4/blob/master/package.json)
    - [Vue](https://vuejs.org/) – frontend framework
      - [Vue Router](https://router.vuejs.org/) – routing package for frontend
      - [Vue Templates](https://vuejs.org/v2/guide/syntax.html) – the syntax for .vue files
      - [Vue Test Utils](https://vue-test-utils.vuejs.org/) – to help testing Vue components
    - [Pinia](https://pinia.vuejs.org/) - global state management
    - [Vite](https://vitejs.dev/) – frontend build tool
    - [Vitest](https://vitest.dev/) – to manage & run the frontend testing
    - [GraphQL](https://graphql.org/) – the communication (query) language for the API
      - [Apollo Client](https://www.apollographql.com/docs/react/) through [Vue Apollo](https://vue-apollo.netlify.com) – frontend GraphQL 
    - [SVGO](https://github.com/svg/svgo) – optimizes SVG files
    - [Tailwind](https://tailwindcss.com/docs/what-is-tailwind/) – utility first styling framework
    - [Lodash](https://lodash.com/) – helper functions for JS
    - [date-fns](https://date-fns.org/) – helper functions for Dates in JS
    - [Faker.js](https://github.com/marak/Faker.js/) – for generating fake data in tests
    - [ESLint](https://eslint.org/) – checks JS for conventions & errors
    - [Stylelint](https://stylelint.io/) – checks CSS for conventions & errors
  - Backend – full list of dependencies can be found in [composer.json](https://github.com/xmmedia/starter_symfony_4/blob/master/composer.json)
    - [Symfony](https://symfony.com/doc/current/index.html#gsc.tab=0) – backend framework
    - [GraphQLBundle](https://github.com/overblog/GraphQLBundle) – provides GraphQL in PHP using [graphql-php](https://github.com/webonyx/graphql-php)
    - [Doctrine](https://www.doctrine-project.org/) – ORM
    - [Doctrine Migrations](https://www.doctrine-project.org/projects/doctrine-migrations.html) – for database migrations
    - [Nelmio CORS Bundle](https://github.com/nelmio/NelmioCorsBundle) – for CORS
    - [Symfony Messenger](https://symfony.com/doc/current/messenger.html) – for async messaging
    - [Symfony Mailer](https://symfony.com/doc/current/mailer.html) – for sending emails
    - [Symfony Security](https://symfony.com/doc/current/security.html) – for authentication & authorization
    - [Twig](https://twig.symfony.com/) – server side templating language (limited use)
    - [Prooph PDO Event Store](https://github.com/prooph/pdo-event-store) & Bridge/Bundle – for doing Event Sourcing
    - [Doctrine](https://www.doctrine-project.org/) – for reading from read models
    - [PhpUnit](https://phpunit.de/) – for running PHP tests
    - [PHP CS](https://cs.sensiolabs.org/) – PHP coding standards analyzer & fixer
    - [PHPStan](https://github.com/phpstan/phpstan) – static analysis of PHP
    - [Symfony Language Tools](https://github.com/symfony/language-tools) – Symfony aware diagnostics (routes, services, Twig, translations, config), in the editor & in CI
    - [Postmark](https://postmarkapp.com/) – for sending email, contains email templates (currently setup under XM Media's account)
    - [Cloudflare](https://www.cloudflare.com/) – DNS & CDN
  - [GitLab](https://gitlab.com/) – deployment
  - [Sentry](https://sentry.io/) – error tracking
  - Dev Tools
    - [Vue Devtools](https://github.com/vuejs/vue-devtools)
    - [Apollo Devtools](https://github.com/apollographql/apollo-client-devtools)

## Updating PHP version

1. Change version in `composer.json` & add polyfill for new PHP version, ie, `symfony/polyfill-php85`.
1. Update the PHP version in the following files:
   - `.ddev/config.yaml`
   - `bin/setup_staging.sh` & `bin/setup_prod.sh` – 4 places each
   - `.gitlab-ci.yml` – 2 places
   - `.github/workflows/ci.yml` – 1 place
   - `.php-cs-fixer.dist.php` – update the `@PHP8#Migration` version to match the current version.
   - `~/.bashrc` and/or `~/.zshrc` on the servers
1. Run `ddev restart` to switch the DDEV container to the new PHP version.
1. Run `ddev composer update` or `composer update` to update the PHP dependencies. If running locally without DDEV, ensure your local PHP version matches the new version.
1. Run the checks `bin/check_full`.
1. Update version in `README.md`.

## User Actions & Fields

### User – Verified
Email address is verified. When the user is added by admin, this is always set to true. When a user registers themselves, this is set to false until they verify their email.

### User – Active
User is active and can log in. When the user is added by admin, this is set to true when they're sent an invitation (invite) or false until they activate their account & set their password. When a user registers themselves, this is set to true.

### Send Activation
Sends email to user to activate their account by entering their password. Uses a reset token and sets their account to active when complete.

### Send Verification
For email/user verification after registering. Sends email to user to activate their account. They don't need to enter a password (already set on registration form). Uses a reset token and sets their account to verified when complete.
