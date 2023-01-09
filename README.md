# Symfony Starter 4

Used to create new projects using [Symfony 5](https://symfony.com/) at [XM Media](https://www.xmmedia.com/).

## Setting Up a New Site

1. Create a new project:
    ```sh
    composer create-project xm/starter_symfony_4 project-name --stability=dev --no-install --remove-vcs
    ```
2. Add `.env.local` – copy `.env` and update.
3. Update `composer.json`: `name`, `license` (likely `private`) and `description`
4. Update `package.json`: `name`, `version`, `git.url`, `license` (probably delete), `private`, `script.dev-server` (update the port)
5. Remove or update the `LICENSE` file.
6. Composer install & update: `composer install && composer update` (or without memory limit: `php -d memory_limit=-1 /usr/local/bin/composer update`)
7. Run `yarn && yarn upgrade`.
8. Run `yarn dev` or `yarn build` (for production) to compile JS & CSS files.
9. Give executable perms to bin dir: `chmod u+x bin/*`
10. Add nitro site: `nitro add` (updating .env won't do anything).
11. Create database with event streams & projections tables from `db_create.sql` using `nitro db import`. 
    - If possible, set database collation to `utf8mb4_bin`: `ALTER DATABASE <database_name> CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;`
12. Create one or more event streams with the command: `bin/console event-store:event-stream:create user && bin/console event-store:event-stream:create auth && bin/console event-store:event-stream:create enquiry` (remove enquiry if not using the enquiry form).
13. Run all projections once: `bin/console event-store:projection:run user_projection -o && bin/console event-store:projection:run user_token_projection -o && bin/console event-store:projection:run enquiry_projection -o` (remove enquiry if not using the enquiry form).
14. Create a user `bin/console app:user:add` (select role `ROLE_SUPER_ADMIN`).
15. Find and make changes near `@todo-symfony` comments throughout the site.
16. Delete starter files: `README.md` (or update) and `TEMPLATES.md`.
17. *Optional:* Run `composer test` – will install PHPUnit & run PHP tests
18. Create new favicons: [realfavicongenerator.net](https://realfavicongenerator.net)
19. Copy (use "Push to another server") or recreate the templates in Postmark. The templates are referenced by the aliases.
20. *Optional:* Run `bin/console app:graphql:dump-schema <username>` to update the GraphQL schema file where `username` is the email of an admin user.
21. Rename the project in PhpStorm.

**Dev site can be accessed at https://[domain]/**

## System Requirements

  - PHP 8.0
  - MySQL 5.7+
  - Node 14
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
  - Makers (PHP):
    - Make aggregate root/model: `bin/console make:model`
    - Make projection: `bin/console make:projection`

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

  - [Lando](https://lando.dev/) – local dev environment
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
