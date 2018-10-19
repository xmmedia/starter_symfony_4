# Symfony Starter 4

Used to create new projects using [Symfony 4](http://symfony.com/) at [XM Media](https://www.xmmedia.com/).

## Setting Up a New Site

1. Download a copy of this repo (probably as a ZIP).
2. Remove or update the `LICENSE` file.
2. [Install Composer](https://getcomposer.org/download/) locally.
3. `composer.json` changes:
  - update the `name`, `license` and `description`
4. Update `package.json`
5. Composer install & update (locally, no autoloader or scripts): `php composer.phar install --no-autoloader --no-scripts && php composer.phar update --no-autoloader --no-scripts`
6. Run `yarn && yarn upgrade` locally (may not be needed).
7. Find and make changes near `@todo-symfony` comments throughout the site.
8. Setup server:
  1. Upload the files (exclude files that are OS dependent like `node_modules` & `app/config/parameters.yml` or that are only for editing like `.idea` and a lot of what's in `.gitignore`).
  2. [Install Composer](https://getcomposer.org/download/)
  3. Install PHP packages/vendors: `php composer.phar install` It will ask for the parameter values including database & SMTP. A secret can be retrieved from http://nux.net/secret
  4. Install NVM: https://github.com/creationix/nvm#install-script
  5. Run `. ./node_setup.sh` (this will setup node & gulp).
  7. Run `npm run dev` or `npm run build` to compile JS & CSS files. (When going live, run `npm run build` instead.)
  8. Create the database: `php bin/console doctrine:schema:create`
  11. Create a user `php bin/console fos:user:create` and then promote them (add the role `ROLE_SUPER_ADMIN`) `php bin/console fos:user:promote`
  12. Setup mail spool: add cron task similar to: `* * * * * cd <path> && php bin/console swiftmailer:spool:send --message-limit=10 --time-limit=45 >> var/log/mailer.log 2>&1`
9. Delete starter files: `README.md` (or update), `TEMPLATES.md`.

**Dev site can be accessed at https://[domain]/app_dev.php/**

## System Requirements

  - PHP 7.1+
  - MySQL 5.6+
  - [Yarn](https://yarnpkg.com/en/docs/install)

## Commands

  - Production JS/CSS build: `yarn run build`
  - Dev JS/CSS build: `yarn run dev`
  - Dev JS/CSS watch: `yarn run watch` (files will not be versioned)
  - Dev JS/CSS HMR server: `yarn run dev-server`
  - Run JS unit tests: `yarn run test`
  - Run PHP unit tests: `php bin/phpunit` or no memory limit `php -d memory_limit=-1 bin/phpunit`
  - Run browser tests (Behat): `php bin/behat`
    - Start Chrome headless first: `/Applications/Google\ Chrome.app/Contents/MacOS/Google\ Chrome --disable-gpu --headless --remote-debugging-address=0.0.0.0 --remote-debugging-port=9222 --window-size-1200,1500`
    
### Apache Config

The following is needed in the Apache VirtualHost for the Webpack Dev Server/HMR to work:

```
  ProxyPassMatch ^(\/dev-server\/.+$)|(sockjs-node) http://localhost:<port>
  ProxyPassReverse / http://localhost:<port>
```

You'll probably want to customize the port number in the Apache ProxyPass config 
and in `package.json` (`script.dev-server`) to be unique to each project if 
running multiple sites on one server.
