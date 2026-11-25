# Upgrading a project to the starter

What a project built from an older starter has to do, & the traps it hits, when it catches up with a starter change.
`/xm:sync-starter` reads the entries after the project's baseline (`composer.json` → `extra.xm-starter.commit`),
`/xm:update-packages` reads it before a major package upgrade, & `/xm:new-project` deletes this file. Newest first.

Add an entry when a project has to do something the starter's diff & the package's own upgrade guide don't make
obvious, or you find a trap while upgrading a site that the next one will hit too. One line each: the trap & its fix.
Rules that always apply go in `/xm:frontend-guidelines` & `/xm:backend-guidelines`, not here.

## Deploy SSH connection (`c263cd8b3`, Nov 2026)

- `bin/ci_ssh_connect` replaces `ssh_retry` & `ssh-agent`: the key is now a file, so anything that relied on the agent
  (forwarding, another job using SSH) breaks. Any other job that connects calls the script itself
- Call it after every step that runs package code, the project's own install & build steps included, so they never
  have the key on disk

## Modal forms (`e83de8b1f`, Nov 2026)

- `common/modal.vue` now stops a submit at the `<dialog>`, so a form in a modal doesn't also submit the page's form
  it's rendered inside. Take it & remove the `.stop`s on each modal's form. Enter in an input in a modal with no form
  of its own still submits the page's form

## Google Maps logo (`f98d06670`, Nov 2026)

- The "Powered by Google" image is out of date. A project's own components that show address suggestions need the
  Google Maps logo too, now rather than at the next sync

## Test helpers in `Xm\SymfonyBundle\Test\` (`01f7d6a9d`, Nov 2026)

- `xm/symfony-bundle` 2.1 moved its test helpers from `Tests\` to `Test\`. Update the `use` statements in the
  project's own tests: the old names are deprecated, & `failOnDeprecation` fails the run once the bundle triggers it

## Auth log user agents (`907bd4506`, Nov 2026)

- `ua-parser-js` 2.x is AGPL: remove it everywhere, not just the auth log. Parse on the server
  (`AuthLog.parsedUserAgent`)

## Messenger failed queue (`a4cdecb9f`, Nov 2026)

- Every project with the async transport (`63cd58656`, Feb 2024) has this bug: `failed` appended
  `?queue_name=failed` to a DSN that already has a query string, so failed messages went back to the async queue &
  `messenger:failed:show` was always empty. Take the starter's `failed` transport & `failure_transport` in
  `messenger.yaml`, keeping the project's routing

## ESLint's @stylistic preset (`60bcb13c3`, Nov 2026)

- Run `yarn lint:js:fix` over the project's own JS & Vue after taking it: the formatting rules changed (spaces inside
  array brackets, aligned values, quotes), so expect most files to change
- "Circular fixes detected" in a `.vue` file is `@stylistic/indent-binary-ops` fighting `vue/script-indent` over a string
  continued with `+` inside a call's arguments: take `2795da210`, which turns it off in `.vue` files

## 1Password on form fields (`900d3a494`, Nov 2026)

- 1Password skips a field with `data-1p-ignore` whatever its value, & Vue renders `false` as `"false"`, so it filled
  nothing. Fix the project's own fields the same way: `:data-1p-ignore="… || null"`

## CORS (`853aed5e3`, Nov 2026)

- `nelmio/cors-bundle` is gone: with `origin_regex` on, the empty `CORS_ALLOW_ORIGIN` default allowed every origin.
  Remove it unless another site's browser JS calls the app; if it does, follow `AGENTS.md` (CORS)
- A failed CSRF check is now a 401, not a 403: check the project's own code for anything that reads the status

## MySQL 8 collations (`11a86c30b`, Nov 2026)

- The starter only changed its own read models. Convert the whole database in one migration: its default, every
  table's & every text column (`utf8mb4_bin` & `utf8_bin` → `utf8mb4_0900_bin`, `utf8mb4_unicode_520_ci` →
  `utf8mb4_0900_ai_ci`), & the project's read models & `db_create.sql` to match. Converting only some tables breaks
  joins & comparisons across them ("Illegal mix of collations")
- Build each `MODIFY` from the column's `SHOW CREATE TABLE` line: the event streams' `aggregate_id` &
  `aggregate_type` are generated columns, & the read models' columns have comments & defaults to keep. Turn off
  `FOREIGN_KEY_CHECKS` while it runs (the OAuth tables' foreign keys), & make it non-transactional & irreversible
- It rebuilds every table, so deploy it with `MAINTENANCE=1`. `doctrine:migrations:migrate --write-sql` runs the
  migration as well as writing the file: snapshot the database first (`ddev snapshot`)

## DDEV (`16c41d157`, Nov 2026)

- Apply as one change with `/xm:lando-to-ddev`, before or after a sync, never piecemeal: a project half on each
  breaks. Keep the Lando versions of the files until then, including in later starter changes to them

## Invokable console commands (`c72843c3f`, Nov 2026)

- Convert all the project's commands at once: while any still extends `Command`, PHPStan's Symfony extension (2.1.1)
  crashes analysing it ("Call to undefined method …Command::setName()"), as it calls `LazyCommand::getCommand()`,
  which returns the raw invokable object

## Symfony 8.1 (`5e2174d95`, Oct 2026)

- `ext-pcntl` is required (the bundle's projection commands): add it to every PHP image that runs the console, CI's
  included

## Setup scripts in `bin/` (`a503e04dd`, Oct 2026)

- The `setup_*` & provisioning scripts moved from the root to `bin/`, some renamed. Move a project's to match, even
  if it never runs them again, so later changes to them sync cleanly

## GraphQL root field access (`f447dfcab`, Sep 2026)

- `Query` & `Mutation` default to admin only. Add `access: '@=true'` to the project's public root fields first, or
  they break for everyone else, & keep `access` explicit on every root field (`RootFieldAccessTest`)
- Hide a field with `access:`, not `public:`, which also leaves it out of `schema.graphql`, so frontend queries using
  it fail lint

## Google Maps address suggestions (`c996241db`, Aug 2026)

- Uses the Places API (New): turn it on in each key's Google Cloud project & add it to the key's API restrictions, or
  requests fail with `PERMISSION_DENIED`
- The key is `VITE_GOOGLE_BROWSER_API_KEY`, a CI/CD variable for each deployed environment (see Vite below)
- Remove the old `gmaps_script` Twig include & any code that called the store from it

## `webmozart/assert` 2 (`fe910a092`, Jul 2026)

- `maxLength()` & the other string assertions reject `null`: use the `nullOr…()` form. Tests & PHPStan rarely catch it

## PHPUnit 13 (`41e571f2b`, Jul 2026)

- Doubles with no expectations must be `createStub()` (& `with()` on a stub errors). Where a mock's expectations
  only apply to some tests, add `#[AllowMockObjectsWithoutExpectations]`

## Symfony 7.4 & PHP 8.5 (`abad91dd7` & `532430f03`, Jul 2026)

- PHP version: follow `AGENTS.md` (Updating PHP Version), plus any config that names a PHP binary or path on the
  server. In a project, bump PHP on its own first (`composer update --lock`), then update packages separately
  (`/xm:update-packages`): a plain `composer update` straight after can pull new majors of indirect dependencies
  along with the PHP change. If `--lock` fails because a locked version is gone from Packagist, run a normal update
  instead
- Recipes add `default_uri: '%env(DEFAULT_URI)%'` to `routing.yaml`. Keep the starter's, built from
  `REQUEST_CONTEXT_SCHEME` & `REQUEST_CONTEXT_HOST`, or links built on the CLI (emails) point to `localhost`
- `composer recipes:update` needs a clean tree (commit each recipe, squash after), & `composer remove` can delete
  the app's own `config/routes/*.yaml` & rewrite `config/bundles.php`: check `git status` after each

## Emails sent from the queue (`82791cd2a`, Jun 2026)

- Each server needs the Messenger worker's cron job (`README.md`; `bin/provision_site` adds it) or no email is sent

## GraphQL query limits (`e46d23b60`, Mar 2026)

- `query_max_complexity: 100` rejects big real queries. Run every frontend query against the limits in a test & raise
  them for the project if needed

## Yarn 4 & the age gate (`e2c238ece`, Oct 2025 & `210d77869`, Nov 2025)

- `npmMinimalAgeGate` (7 days) rejects lock entries newer than that (`YN0016 … quarantined`), one round at a time.
  Delete those entries from `yarn.lock` & let Yarn resolve the newest allowed version; don't turn the gate off
- `yarn npm audit` replaces audit-ci: move the project's allowlist to `npmAuditIgnoreAdvisories` in `.yarnrc.yml`

## Tailwind 4 (`2c9447559`, Jun 2025)

- Run `@tailwindcss/upgrade`, then check its output by hand: it misread `!visible` & some `@apply` names
- Unlayered CSS now beats utilities: put the project's own CSS in `@layer base` or `@layer components`, as the
  starter does. Utilities then beat components (see `/xm:frontend-guidelines`)
- Most of what breaks is visual & no check catches it: compare before & after screenshots of each page

## Doctrine ORM 3 & DBAL 4 (`2e382b0c6`, Jun 2025)

- `Statement::executeQuery()` no longer takes parameters & PHP ignores the extra argument, so placeholders stay
  unbound: bind them first
- `datetime` rejects immutable values, including in projections' column type maps (see `/xm:backend-guidelines`)
- Tests don't catch either: run the real queries (a console command, a GraphQL request) against a database

## PHPStan 2 & Rector 2 (`f9db45bd3`, Jun 2025)

- Move to both together: Rector 0.18 needs PHPStan 1, & PHPStan 1 can't analyse newer Symfony
- After a Rector pass, check the tests still assert something (`--fail-on-risky`): its rewrites have dropped
  `$result =` & `assertInstanceOf()`, leaving tests that pass & check nothing

## Pinia (`d4cf08b13`, Apr 2023)

- Twig that called Vuex (`App.$store.commit(…)`) fails silently after the switch: move it to the Pinia store

## Vite (`50577f3c7`, Apr 2023)

- `VITE_` env vars are built into the JS in CI, so they're GitLab CI/CD variables per environment, not values in the
  server's `.env.local`. Without one the deployed site sends `undefined`
- Point `EntrypointIntegrity` at the bundle's Vite query: the Encore one returns `null` with Vite, so the
  changed-build reload never runs

## Vue 3, Vuelidate 2 & vue-router 4 (`a77d26c19`, Apr 2023)

The official migration guides cover the API changes. These pass lint & the build, then fail quietly:

- Vue 3 condenses whitespace between elements on separate lines, so inline elements run together (pills, badges,
  icons beside text): fix the markup (a literal space where text needs one, gap or margin between elements), as the
  starter's templates do. Don't set the compiler's `whitespace: 'preserve'`, & remove it where a site already has it,
  so the config stays the starter's
- vue-router 4 adds the `?` itself: a custom `stringifyQuery` mustn't, or URLs get `??` & filters are lost on reload
- Vuelidate 2 has no `$anyError`, so a check on it is always falsy & the form sends invalid data: use `$error` or
  `$invalid`
- `$touch()` & `$invalid` don't wait for async rules: submit with `await v$.value.$validate()` (see
  `/xm:frontend-guidelines` for async rules & browser validation)
- Rules can't read `this`: write rule files as functions that take refs or getters (`requiredIf(() => …)`,
  `sameAs(toRef(…))`), as the starter's user rules do. `between()` given plain values keeps its first limits
- No `$each`: validate each item of a list in its own component, which the parent's `v$` collects
- A child's rules roll up into the parent's `v$`: a modal with its own form needs `{ $stopPropagation: true }`, not
  `$scope: false`, which also stops it collecting its own children
- Reading a rule a field doesn't have throws: see `/xm:frontend-guidelines` (Vuelidate)

## Packages some projects use

- **Stripe PHP SDK**: newer majors always send their own pinned API version (old ones sent none, so the account's
  default applied), so an SDK update is an API upgrade: read Stripe's changelog between the two versions. Webhook
  endpoints stay on the version they were created with until replaced
- **Google Analytics 4**: `window.ga` doesn't exist with `gtag`, so code calling `ga()` sends nothing. Enhanced
  measurement may count route changes itself, so a manual `page_view` can double count
