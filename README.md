# Symfony Starter 4

Used to create new projects using [Symfony 4](http://symfony.com/) at [XM Media](https://www.xmmedia.com/).

## Setting Up a New Site

1. Download a copy of this repo (probably as a ZIP).
2. Remove or update the `LICENSE` file.
3. [Install Composer](https://getcomposer.org/download/) locally.
4. Update `composer.json`: `name`, `license` (likely `private`) and `description`
5. Update `package.json`: `name`, `version`, `git.url`, `license`, `private`, `script.dev-server`
6. Composer install & update (locally): `composer install && composer update` (or without memory limit: `php -d memory_limit=-1 /usr/local/bin/composer update`)
7. Run `yarn && yarn upgrade` locally.
8. Find and make changes near `@todo-symfony` comments throughout the site.
9. Setup server:
   1. Upload the files (exclude files that are OS dependent like `node_modules` & `.env` or that are only for editing like `.idea` and a lot of what's in `.gitignore`).
   2. [Install Composer](https://getcomposer.org/download/)
   3. Install PHP packages/vendors: `php composer.phar install`
   4. Update `.env`.
   5. Install NVM: https://github.com/creationix/nvm#install-script
   6. Run `. ./node_setup.sh` (this will setup node & install the JS packages).
   7. Run `yarn dev` or `yarn build` (for production) to compile JS & CSS files.
   9. Create event streams & projections tables from: https://github.com/prooph/pdo-event-store/tree/master/scripts/mysql
   10. Create one or more event streams with the command `bin/console event-store:event-stream:create user && bin/console event-store:event-stream:create auth` (and if using enquiry: `bin/console event-store:event-stream:create enquiry`)
   11. Run all projections once:
       1. `bin/console event-store:projection:run user_projection -o` 
       2. `bin/console event-store:projection:run user_token_projection -o` 
       3. `bin/console event-store:projection:run enquiry_projection -o` 
   12. Create a user `bin/console app:user:create` & run user projection: `bin/console event-store:projection:run user_projection -o`
   13. Copy or recreate the templates in Postmark & update the template IDs (see `@todo-symfony`).
   14. Setup supervisord:
       1. Write supervisord config: `bin/console app:supervisor:write-config`
       2. Add site supervisord config to main config, for example `files = /etc/supervisord/*.conf /home/user/supervisord.conf` (as root)
       3. Tell supervisord to read the config: `supervisorctl reread && supervisorctl update` (as root)
       4. Ensure programs are running: `supervisorctl status` 
   15. Setup mail spool: add cron task similar to: `*/15 * * * * cd /home/user/example.com/current && bin/console swiftmailer:spool:send --message-limit=10 --time-limit=45 >> var/log/mailer.log 2>&1` (this only sends error emails, runs every 15 minutes)
   13. Add logrotate cron: `30 4 * * 1 cd /home/user/example.com/current && logrotate app/config/packages/logrotate.conf --state var/logrotate-state` (runs Mondays at 04:30 UTC)
10. Delete starter files: `README.md` (or update) and `TEMPLATES.md`.
11. Run `composer test` – will install PHPUnit & run PHP tests
12. Create new favicons: [realfavicongenerator.net](https://realfavicongenerator.net)

**Dev site can be accessed at https://[domain]/**

## System Requirements

  - PHP 7.3+
  - MySQL 5.7+
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
    - [GraphQiL](https://github.com/graphql/graphiql) is available at `/graphiql`
