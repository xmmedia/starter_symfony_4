# Symfony Starter 4

Used to create new projects using [Symfony 4](http://symfony.com/) at [XM Media](https://www.xmmedia.com/).

## Setting Up a New Site

1. Create a new project:
    ```sh
    composer create-project xm/starter_symfony_4 project-name --stability=dev --no-install --remove-vcs
    ```
2. Setup dev server:
   1. If using InterWorx, upload `setup_dev.sh` and run: `sh ./setup_dev.sh` 
   1. Upload the files (exclude files that are OS dependent like `node_modules` & `.env.local` or that are only for editing like `.idea` and a lot of what's in `.gitignore`).
   2. [Install Composer](https://getcomposer.org/download/) (if not already installed)
   3. Install PHP packages/vendors: `php composer.phar install`
   4. Add `.env.local` (copy `.env` and update). Generate this using 1Password (no need to store it) or similar at about 32 characters containing letters, numbers and symbols.
   6. Run `. ./node_setup.sh` (this will setup node & install the JS packages).
   7. Run `yarn dev` or `yarn build` (for production) to compile JS & CSS files.
   8. Give executable perms to bin dir: `chmod u+x bin/*`
   9. Create event streams & projections tables from `db_create.sql`. Set database collation to `utf8mb4_bin`.
   10. Create one or more event streams with the command `bin/console event-store:event-stream:create user && bin/console event-store:event-stream:create auth && bin/console event-store:event-stream:create enquiry` (remove enquiry if not using the enquiry form).
   11. Run all projections once: `bin/console event-store:projection:run user_projection -o && bin/console event-store:projection:run user_token_projection -o && bin/console event-store:projection:run enquiry_projection -o` 
   12. Create a user `bin/console app:user:add` (select role `ROLE_SUPER_ADMIN`).
   13. Setup mail spool: add cron task similar to: `*/15 * * * * cd /home/user/example.com/current && bin/console swiftmailer:spool:send --message-limit=10 --time-limit=45 >> var/log/mailer.log 2>&1` (this only sends error emails, runs every 15 minutes)
      1. As one command: `crontab -l > mycron; echo "*/15 * * * * cd ${BASE}/current && bin/console swiftmailer:spool:send --message-limit=10 --time-limit=45 >> var/log/mailer.log 2>&1" >> mycron; crontab mycron; rm mycron`
   14. Add logrotate cron (only needed on production): `30 4 * * 1 cd /home/user/example.com/current && logrotate app/config/packages/logrotate.conf --state var/logrotate-state` (runs Mondays at 04:30 UTC)
3. Remove or update the `LICENSE` file.
4. [Install Composer](https://getcomposer.org/download/) locally.
5. Update `composer.json`: `name`, `license` (likely `private`) and `description`
6. Update `package.json`: `name`, `version`, `git.url`, `license`, `private`, `script.dev-server`
7. Composer install & update (locally): `composer install && composer update` (or without memory limit: `php -d memory_limit=-1 /usr/local/bin/composer update`)
8. Run `yarn && yarn upgrade` locally.
9. Find and make changes near `@todo-symfony` comments throughout the site.
10. Delete starter files: `README.md` (or update) and `TEMPLATES.md`.
11. *Optional:* Run `composer test` – will install PHPUnit & run PHP tests
12. Create new favicons: [realfavicongenerator.net](https://realfavicongenerator.net)
13. Copy (use "Push to another server") or recreate the templates in Postmark. The templates are referenced by the aliases.
14. *Optional:* Run `bin/console app:graphql:dump-schema <username>` to update the GraphQL schema file where `username` is the email of an admin user.

**Dev site can be accessed at https://[domain]/**

## System Requirements

  - PHP 7.3+
  - MySQL 5.7+
  - Node 10
  - [Yarn](https://yarnpkg.com/en/docs/install)

## Commands

  - Production JS/CSS build: `yarn build`
  - Dev JS/CSS build: `yarn dev`
  - Dev JS/CSS watch: `yarn watch` (files will not be versioned)
  - Dev JS/CSS HMR server: `yarn dev-server` (see below)
  - JS Tests ([Jest](https://jestjs.io/)): `yarn test:unit`
  - E2E Tests ([Cypress](https://www.cypress.io/)): `yarn test:e2e`
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
    - All commands: `bin/console event-store:projection`
    - Run once: `bin/console event-store:projection:run user_projection -o`

### Apache Config

The following is needed in the Apache VirtualHost for the Webpack Dev Server/HMR to work:

```
ProxyPassMatch ^(\/dev-server\/.+$)|(sockjs-node) http://localhost:<port>
ProxyPassReverse / http://localhost:<port>
```

You'll probably want to customize the port number in the Apache ProxyPass config
and in `package.json` (`script.dev-server`) to be unique to each project if
running multiple sites on one server.

## Incorporated Libraries & Tools

  - Frontend – full list of dependencies can be found in [package.json](https://github.com/xmmedia/starter_symfony_4/blob/master/package.json)
    - [Vue](https://vuejs.org/) – frontend framework
      - [Vue Router](https://router.vuejs.org/) – routing package for frontend
      - [Vuex](https://vuex.vuejs.org/) – helps to manage state
      - [Vue Devtools](https://github.com/vuejs/vue-devtools) – makes debugging in the browser easier
      - [Vue Templates](https://vuejs.org/v2/guide/syntax.html) – the syntax for .vue files
      - [Vue Test Utils](https://vue-test-utils.vuejs.org/) – to help testing Vue components
    - [Vue CLI](https://cli.vuejs.org/) – to manage & run the frontend compilation & testing
    - [GraphQL](https://graphql.org/) – the communication (query) language for the API
      - [Apollo Client](https://www.apollographql.com/docs/react/) through [Vue Apollo](https://vue-apollo.netlify.com) – frontend GraphQL 
    - [SASS](https://sass-lang.com/) – CSS preprocessor (uses [node-sass](https://www.npmjs.com/package/node-sass))
    - [Webpack](https://webpack.js.org/) – compiles JS & CSS
      - [Babel](https://babeljs.io/) – transforms JS to work in all browsers
      - [Webpack Encore](https://symfony.com/doc/current/frontend.html) – connects the frontend and backend and makes Webpack configuration simpler
      - [PostCSS](https://github.com/postcss/postcss) – transforms CSS
      - [Autoprefixer](ub.com/postcss/autoprefixer) – for adding browser prefixes
      - [Purge CSS](https://github.com/FullHuman/purgecss) – removes unused CSS during the deployment process (not run in dev)
      - [SVGO](https://github.com/svg/svgo) – optimizes SVG files
      - [Bundle Analyzer](https://github.com/webpack-contrib/webpack-bundle-analyzer) – displays sizes/stats on the JS bundle size
    - [Tailwind](https://tailwindcss.com/docs/what-is-tailwind/) – utility first styling framework
    - [Jest](https://jestjs.io/) – JS unit testing
    - [Cypress](https://www.cypress.io/) – end-to-end (e2e) testing
    - [Lodash](https://lodash.com/) – helper functions for JS
    - [date-fns](https://date-fns.org/) – helper functions for Dates in JS
    - [PortalVue](https://github.com/LinusBorg/portal-vue) – helps to manage things like modals
    - [Vue-JS-Modal](http://vue-js-modal.yev.io/) – for modals 
    - [Workbox](https://developers.google.com/web/tools/workbox/) – helps with configuration of service worker/PWA
    - [Faker.js](https://github.com/marak/Faker.js/) – for generating fake data in tests
    - [ESLint](https://eslint.org/) – checks JS for conventions & errors
    - [Stylelint](https://stylelint.io/) – checks CSS for conventions & errors
  - Backend – full list of dependencies can be found in [composer.json](https://github.com/xmmedia/starter_symfony_4/blob/master/composer.json)
    - [Symfony](https://symfony.com/doc/current/index.html#gsc.tab=0) – backend framework
    - [GraphQLBundle](https://github.com/overblog/GraphQLBundle) – provides GraphQL in PHP using [graphql-php](https://github.com/webonyx/graphql-php)
      - [GraphQiL](https://github.com/graphql/graphiql) is available at `/graphiql` (on dev only)
    - [Twig](https://twig.symfony.com/) – server side templating language (limited use)
    - [Prooph PDO Event Store](https://github.com/prooph/pdo-event-store) & Bridge/Bundle – for doing Event Sourcing
    - [Doctrine](https://www.doctrine-project.org/) – for reading from read models
    - [PhpUnit](https://phpunit.de/) – for running PHP tests
    - [PHP CS](https://cs.sensiolabs.org/) – PHP coding standards analyzer & fixer
    - [PHPStan](https://github.com/phpstan/phpstan) – static analysis of PHP
    - [Postmark](https://postmarkapp.com/) – for sending email, contains email templates (currently setup under XM Media's account)
    - [Cloudflare](https://www.cloudflare.com/) – DNS & CDN
  - [GitLab](https://gitlab.com/) – deployment
  - Dev Tools
    - [Vue Devtools](https://github.com/vuejs/vue-devtools)
    - [Apollo Devtools](https://github.com/apollographql/apollo-client-devtools)
