# Symfony Starter 4

Used to create new projects using [Symfony 4](http://symfony.com/) at [XM Media](https://www.xmmedia.com/).

## Setting Up a New Site

1. Download a copy of this repo (probably as a ZIP).
2. Remove or update the `LICENSE` file.
3. [Install Composer](https://getcomposer.org/download/) locally.
4. Update `composer.json`: `name`, `license` (likely `private`) and `description`
5. Update `package.json`: `name`, `version`, `git.url`, `license`, `private`, `script.dev-server`
6. Composer install & update (locally, no autoloader or scripts): `composer install && php -d memory_limit=-1 /usr/local/bin/composer update`
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
   8. Create the database: `bin/console doctrine:schema:create`
   9. Create event streams & projections tables from: https://github.com/prooph/pdo-event-store/tree/master/scripts/mysql
   10. Create one or more event streams with the command `bin/console event-store:event-stream:create enquiry_event`
   10. Create a user `bin/console app:user:create`
   11. Copy or recreate the templates in Postmark & update the template IDs (see `@todo-symfony`).
   12. Setup supervisord:
       1. Write supervisord config: `bin/console app:supervisor:write-config`
       2. Add site supervisord config to main config, for example `files = /etc/supervisord/*.conf /home/user/supervisord.conf` (as root)
       3. Tell supervisord to read the config: `supervisorctl reread && supervisorctl update` (as root)
       4. Ensure programs are running: `supervisorctl status` 
   12. Setup mail spool: add cron task similar to: `* * * * * cd <path> && bin/console swiftmailer:spool:send --message-limit=10 --time-limit=45 >> var/log/mailer.log 2>&1`
   13. Add logrotate cron: `30 4 * * 1 cd /home/user/example.com/current && logrotate app/config/logrotate.conf --state var/logrotate-state`
10. Delete starter files: `README.md` (or update), `TEMPLATES.md`, `LICENSE`.

**Dev site can be accessed at https://[domain]/**

## System Requirements

  - PHP 7.2+
  - MySQL 5.7+
  - [Yarn](https://yarnpkg.com/en/docs/install)

## Commands

  - Production JS/CSS build: `yarn build`
  - Dev JS/CSS build: `yarn dev`
  - Dev JS/CSS watch: `yarn watch` (files will not be versioned)
  - Dev JS/CSS HMR server: `yarn dev-server` (see below)
  - Testing:
    - JS unit tests: `yarn test:unit`
    - E2E tests using Cypress: `yarn test:e2e`
  - Linting:
    - JS: `yarn lint:js` or `yarn lint:js:fix`
    - CSS: `yarn lint:css` or `yarn lint:css:fix`
  - PHPUnit: 
    - `composer test`
    - no memory limit `php -d memory_limit=-1 bin/simple-phpunit`
    - with coverage (HTML) `composer test:coverage`
  - PHP CS Fixer: (must be installed first)
    - Dry run: `composer cs`
    - Fix: `composer cs:fix`
  - PHP Static Analysis (PHPStan): `composer static`
  - Projections:
    - All commands: `bin/console event-store:projection`
    - Run once: `bin/console event-store:projection:run enquiry_projection -o`

### Apache Config

The following is needed in the Apache VirtualHost for the Webpack Dev Server/HMR to work:

```
  ProxyPassMatch ^(\/dev-server\/.+$)|(sockjs-node) http://localhost:<port>
  ProxyPassReverse / http://localhost:<port>
```

You'll probably want to customize the port number in the Apache ProxyPass config
and in `package.json` (`script.dev-server`) to be unique to each project if
running multiple sites on one server.
