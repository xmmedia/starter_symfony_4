# Symfony Starter 4

Used to create new projects using [Symfony 6](https://symfony.com/) at [XM Media](https://www.xmmedia.com/).

Dev: https://symfonystarter.lndo.site @todo-symfony  
Staging: @todo-symfony  
Production: @todo-symfony

## Setting Up a New Site

1. Create a new project:
    ```sh
    composer create-project xm/starter_symfony_4 project-name --stability=dev --no-install --remove-vcs
    ```
2. Add `.env.local` – copy `.env` and update.
3. Update `composer.json`: `name`, `license` (likely `private`) and `description`
4. Update `package.json`: `name`, `version`, `git.url`, `license` (probably delete), `private`
5. Update the port in `vite.config.js` (`server.port` and `server.origin`)
6. Remove or update the `LICENSE` file.
7. Composer install & update: `composer install && composer update` (or without memory limit: `php -d memory_limit=-1 /usr/local/bin/composer update`)
8. Run `yarn && yarn up -R "**"`.
9. Run `yarn dev` or `yarn build` (for production) to compile JS & CSS files.
10. Give executable perms to bin dir: `chmod u+x bin/*` (helpful, but optional)
11. Run/Start Lando site: `lando start` 
12. Create database with event streams & projections tables from `db_create.sql` using `lando db-import db_create_sql`. 
    - If possible, set database collation to `utf8mb4_bin`: `ALTER DATABASE <database_name> CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;`
13. Create the required event streams with the command: `bin/console event-store:event-stream:create user && bin/console event-store:event-stream:create auth` (or if using lando: `lando console event-store:event-stream:create user && lando console event-store:event-stream:create auth`).
14. Run all projections once: `bin/console event-store:projection:run user_projection -o` (or if using lando: `lando console event-store:projection:run user_projection -o`).
15. Create a user `bin/console app:user:add` (select role `ROLE_SUPER_ADMIN`).
16. Find and make changes near `@todo-symfony` comments throughout the site.
17. Delete starter files: `README.md` (or update) and `TEMPLATES.md`.
18. *Optional:* Run `composer test` – will install PHPUnit & run PHP tests
19. Create new favicons: [realfavicongenerator.net](https://realfavicongenerator.net)
20. Copy (use "Push to another server") or recreate the templates in Postmark. The templates are referenced by the aliases.
21. *Optional:* Run `bin/console app:graphql:dump-schema <username>` to update the GraphQL schema file where `username` is the email of an admin user.
22. *Optional:* Rename the project in PhpStorm.

**Local dev site can be accessed at: https://[domain]/**

## Setting Up Starter

1. Add `.env.local` – copy `.env` and update.
2. Composer install: `composer install`
3. Ensure correct node version: `nvm use`
4. Run `yarn`.
5. Run `yarn dev` or `yarn build` (for production) to compile JS & CSS files.
6. Give executable perms to bin dir: `chmod u+x bin/*` (helpful, but optional)
7. Run/Start Lando site: `lando start` 
8. Create database with event streams & projections tables from `db_create.sql` using `lando db-import db_create_sql`. 
    - If possible, set database collation to `utf8mb4_bin`: `ALTER DATABASE <database_name> CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;` This can be done through PhpMyAdmin (link provided by `lando start` command above or `lando info`)
9. Create the required event streams with the command: `bin/console event-store:event-stream:create user && bin/console event-store:event-stream:create auth` (or if using lando: `lando console event-store:event-stream:create user && lando console event-store:event-stream:create auth`).
10. Run all projections once: `bin/console event-store:projection:run user_projection -o` (or if using lando: `lando console event-store:projection:run user_projection -o`).
11. Create a user `bin/console app:user:add` (select role `ROLE_SUPER_ADMIN`).
12. *Optional:* Run `composer test` – will install PHPUnit & run PHP tests.
13. Run `bin/check` to run all code tests/checks.

**Local dev site can be accessed at: https://symfonystarter.lndo.site

## System Requirements

  - PHP 8.2
  - MySQL 5.7+
  - Node 18 + [nvm](https://github.com/nvm-sh/nvm)
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
  - PHP Tests ([PhpUnit](https://phpunit.de/)): 
    - `composer test`
    - no memory limit `php -d memory_limit=-1 bin/simple-phpunit`
    - with coverage (HTML) `composer test:coverage`
  - [PHP CS](https://cs.sensiolabs.org/): (must be installed first)
    - Dry run: `composer cs`
    - Fix: `composer cs:fix`
  - PHP Static Analysis ([PHPStan](https://github.com/phpstan/phpstan)): `composer static`
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
