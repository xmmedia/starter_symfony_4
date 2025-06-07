# Symfony Starter

Used to create new projects using [Symfony 6](https://symfony.com/) at [XM Media](https://www.xmmedia.com/).

Dev: https://symfonystarter.lndo.site @todo-symfony  
Staging: @todo-symfony  
Production: @todo-symfony

## Setting Up a New Site

_Note:_ Make sure your git configuration is set to use the correct line endings: `git config --global core.autocrlf input && git config --global core.eol lf`

1. Create a new project:
    ```sh
    composer create-project xm/starter_symfony_4 project-name --stability=dev --no-install --remove-vcs
    ```
1. Add `.env.local` – copy `.env` and update.
1. Update `composer.json`: `name`, `license` (likely `private`) and `description`
1. Update `package.json`: `name`, `version`, `git.url`, `license` (probably delete), `private`
1. Update the port in `vite.config.js` (`server.port` and `server.origin`)
1. Remove or update the `LICENSE` file.
1. Composer install & update: `lando composer install && lando composer update` (or remove `lando` to run without Lando or without memory limit: `php -d memory_limit=-1 /usr/local/bin/composer update`)
1. Run `yarn && yarn up -R "**"`.
1. Run `yarn dev` or `yarn build` (for production) to compile JS & CSS files.
1. Give executable perms to bin dir: `chmod u+x bin/*` (helpful, but optional)
1. Run/Start Lando site: `lando start` 
1. Create database with event streams & projections tables from `db_create.sql` using `lando db-import db_create_sql`. 
    - If possible, set database collation to `utf8mb4_bin`: `ALTER DATABASE <database_name> CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;`
1. Create the required event streams with the command: `bin/console event-store:event-stream:create user && bin/console event-store:event-stream:create auth` (or if using lando: `lando console event-store:event-stream:create user && lando console event-store:event-stream:create auth`).
1. Run all projections once: `bin/console event-store:projection:run user_projection -o && bin/console event-store:projection:run auth_projection -o` (or if using lando: `lando console event-store:projection:run user_projection -o && lando console event-store:projection:run auth_projection -o`).
1. Create a user `bin/console app:user:add` (select role `ROLE_SUPER_ADMIN`) (or if using lando: `lando console app:user:add`).
1. Find and make changes near `@todo-symfony` comments throughout the site.
1. Delete starter files: `README.md` (or update) and `TEMPLATES.md`.
1. *Optional:* Run `composer test` – will install PHPUnit & run PHP tests
1. Create new favicons: [realfavicongenerator.net](https://realfavicongenerator.net)
1. Copy (use "Push to another server") or recreate the templates in Postmark. The templates are referenced by the aliases.
1. *Optional:* Run `bin/console app:graphql:dump-schema <username>` to update the GraphQL schema file where `username` is the email of an admin user.
1. *Optional:* Rename the project in PhpStorm.

**Local dev site can be accessed at: https://[domain]/**

## Setting Up Starter

1. Checkout the repo.
1. Add `.env.local` – copy `.env` and update.
1. Run/Start Lando site: `lando start`
1. Composer install: `lando composer install` or `composer install` to run without Lando.
1. Ensure correct node version: `nvm use`
1. Run `yarn`.
1. Run `yarn dev` or `yarn build` (for production) to compile JS & CSS files.
1. Give executable perms to bin dir: `chmod u+x bin/*` (helpful, but optional)
1. Create database with event streams & projections tables from `db_create.sql` using `lando db-import db_create_sql`. 
    - If possible, set database collation to `utf8mb4_bin`: `ALTER DATABASE <database_name> CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;` This can be done through PhpMyAdmin (link provided by `lando start` command above or `lando info`)
1. Create the required event streams with the command: `bin/console event-store:event-stream:create user && bin/console event-store:event-stream:create auth` (or if using Lando: `lando console event-store:event-stream:create user && lando console event-store:event-stream:create auth`).
1. Run all projections once: `bin/console event-store:projection:run user_projection -o && bin/console event-store:projection:run auth_projection -o` (or if using lando: `lando console event-store:projection:run user_projection -o && lando console event-store:projection:run auth_projection -o`).
1. Create a user `bin/console app:user:add` (select role `ROLE_SUPER_ADMIN`) (or if using Lando: `lando console app:user:add`).
1. *Optional:* Run `composer test` – will install PHPUnit & run PHP tests – or `lando composer test` if using Lando.
1. Run `bin/check` to run all code tests/checks.

**Local dev site can be accessed at: https://symfonystarter.lndo.site

## System Requirements

### Server

  - PHP 8.3
  - MySQL 8.0

### Locally for Development

  - [Lando](https://lando.dev/)
  - [Node 18](https://nodejs.org/)
  - [nvm](https://github.com/nvm-sh/nvm)
  - [Yarn v3](https://v3.yarnpkg.com/getting-started/install)

## Commands

  - Check all code: `bin/check`
  - Production JS/CSS build: `yarn build`
  - Dev JS/CSS build: `yarn dev` (recommended command: `nvm use && yarn && yarn dev`)
  - Preview production JS/CSS build: `yarn preview`
  - JS Tests ([Jest](https://jestjs.io/)): `yarn test:unit`
  - Linting:
    - JS ([ESLint](https://eslint.org/)): `yarn lint:js` or `yarn lint:js:fix`
    - CSS: `yarn lint:css` or `yarn lint:css:fix`
  - Install PHP packages: `lando composer install` or `composer install`
  - Install JS packages: `yarn`
  - PHP Tests ([PhpUnit](https://phpunit.de/)): 
    - `lando composer test` or `composer test`
    - no memory limit `php -d memory_limit=-1 bin/phpunit`
    - with coverage (HTML) `composer test:coverage`
    - show deprecations `composer test:deprecations`
  - [PHP CS](https://cs.sensiolabs.org/): (must be installed first)
    - Dry run: `lando composer cs` or `composer cs`
    - Fix: `lando composer cs:fix` or `composer cs:fix`
  - PHP Static Analysis ([PHPStan](https://github.com/phpstan/phpstan)): `lando composer static` or `composer static`
  - Projections:
    - Show all commands: `bin/console event-store:projection`
    - Run once: `bin/console event-store:projection:run user_projection -o`
  - Makers (PHP):
    - Make aggregate root/model: `bin/console make:model`
    - Make projection: `bin/console make:projection`
  - Upgrade JS packages: `yarn up -R "**"`
    - Upgrade a specific package: `yarn up -R "package-name"`
    - Upgrade major versions: `yarn upgrade-interactive` (ctrl+c to exit without changes)

## Incorporated Libraries & Tools

  - [Lando](https://lando.dev/) – local dev environment
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
    - [SASS](https://sass-lang.com/) – CSS preprocessor (uses [node-sass](https://www.npmjs.com/package/node-sass))
    - [PostCSS](https://github.com/postcss/postcss) – transforms CSS
    - [Autoprefixer](ub.com/postcss/autoprefixer) – for adding browser prefixes
    - [SVGO](https://github.com/svg/svgo) – optimizes SVG files
    - [Tailwind](https://tailwindcss.com/docs/what-is-tailwind/) – utility first styling framework
    - [Jest](https://jestjs.io/) – JS unit testing
    - [Lodash](https://lodash.com/) – helper functions for JS
    - [date-fns](https://date-fns.org/) – helper functions for Dates in JS
    - [Vue Final Modal](https://vue-final-modal.org/) – for modals 
    - [Faker.js](https://github.com/marak/Faker.js/) – for generating fake data in tests
    - [ESLint](https://eslint.org/) – checks JS for conventions & errors
    - [Stylelint](https://stylelint.io/) – checks CSS for conventions & errors
  - Backend – full list of dependencies can be found in [composer.json](https://github.com/xmmedia/starter_symfony_4/blob/master/composer.json)
    - [Symfony](https://symfony.com/doc/current/index.html#gsc.tab=0) – backend framework
    - [GraphQLBundle](https://github.com/overblog/GraphQLBundle) – provides GraphQL in PHP using [graphql-php](https://github.com/webonyx/graphql-php)
      - [GraphQiL](https://github.com/graphql/graphiql) is available at `/graphiql` (on dev only)
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
    - [Postmark](https://postmarkapp.com/) – for sending email, contains email templates (currently setup under XM Media's account)
    - [Cloudflare](https://www.cloudflare.com/) – DNS & CDN
  - [GitLab](https://gitlab.com/) – deployment
  - [Sentry](https://sentry.io/) – error tracking
  - Dev Tools
    - [Vue Devtools](https://github.com/vuejs/vue-devtools)
    - [Apollo Devtools](https://github.com/apollographql/apollo-client-devtools)
