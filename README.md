# plugins.cakephp.org

This repository contains the code responsible for https://plugins.cakephp.org

## Request handling

Public catalogue requests use CakePHP's `RateLimitMiddleware` with the existing
atomic Redis counter. `RATE_LIMIT_CACHE_URL` selects the cache, defaulting to
Dokku's `REDIS_URL`. Rate limiting is enabled when a Redis URL is configured;
`RATE_LIMIT_ENABLED=false` disables it for local development or maintenance.
The production container includes the PHP Redis extension.

The limits are 90 browsing requests, 15 filtered listings and 30 Interact updates
per minute per client IP. Direct plugin detail pages use the browsing limit.
`TRUSTED_PROXY_IPS` lists proxies allowed to supply `X-Forwarded-For` and defaults
to `172.17.0.1` for the Dokku setup. Only include proxies that overwrite that header.

Interactive regions use Cake Interact's shipped runtime. Catalogue input
validation remains in the islands and the framework paginator.

## Starting local development

You need PHP 8.5 and [DDEV](https://docs.ddev.com/en/stable/) installed and configured on your machine.

After that start the app via `ddev start` and install the PHP dependencies and runtime assets:

```bash
ddev exec composer install
ddev exec bin/cake plugin assets symlink Interact
```

Next you need to apply migrations to create the database tables:

```bash
ddev exec bin/cake migrations migrate
ddev exec bin/cake migrations migrate -p Tags
ddev exec bin/cake migrations migrate -p ADmad/SocialAuth
```

After that you can perform a fresh sync via

```bash
ddev exec bin/cake sync_packages
```

While that is running (takes quite a while) you should install node modules and start the dev server via

```bash
ddev exec npm ci
ddev exec npm run dev
```

Open the DDEV site URL while Vite is running. DDEV exposes Vite on HTTPS port
5173; restart DDEV after changing its port configuration. Development uses
`DEBUG=true`. CakeVite loads the dev server and provides CSS hot reload.

For a production preview, run `npm run build` and use `?vprod=1` on the site URL.
With `DEBUG=false`, CakeVite always uses the built assets. Builds write hashed
JavaScript, CSS, and the Vite manifest into `webroot/build`.

Outside DDEV, run `npm run dev` alongside the PHP server. Vite defaults to
`http://localhost:5173`; set `VITE_DEV_SERVER_URL` in the PHP environment when
using a different address.

## Categories and Awesome CakePHP

`sync_packages` refreshes Packagist packages first, then imports categories from
the Plugins section of [Awesome CakePHP](https://github.com/FriendsOfCake/awesome-cakephp).
Run either source independently when needed:

```bash
ddev exec bin/cake sync_packages --source packagist
ddev exec bin/cake sync_packages --source awesome
```

Apply migrations before the first category import. The Awesome importer matches
primary GitHub repository links to existing packages; it does not create packages.
Plugins can belong to several categories. Categories are stored separately from
compatibility tags, so Packagist updates preserve them.

The **Awesome CakePHP** badge means inclusion in that community-curated list,
not a score or certification. Plugins without a badge remain in the directory.
Category and Awesome-only filters combine with search and compatibility filters.
Browse categories in the expandable grid, with directory-wide plugin counts and
an optional local search. Selecting a category collapses the grid; its chip beside
the results count clears that category while retaining other filters.

A failed fetch, invalid source, or failed write retains the previous curation.
Unmatched and ambiguous links are summarized; use `--verbose` to see them.
Resolve moved or shared repositories with explicit URL-to-Composer-name mappings
in `Packages.awesomeAliases` in `config/app.php`:

```php
'awesomeAliases' => [
    'https://github.com/old-owner/plugin' => 'current-vendor/plugin',
],
```

Both importers and the Markdown parser are registered in `Application::services()`.
The console command receives its importers through CakePHP's container.

## Featured plugin logos

Set an optional logo URL or image path alongside the package name in
`Packages.featured` in `config/app.php`:

```php
'featured' => [
    'cakedc/users' => ['logo' => '/img/plugins/users.svg'],
    'vendor/plugin' => ['logo' => 'https://example.com/plugin-logo.png'],
    'friendsofcake/crud' => ['logo' => null],
],
```

A null or broken logo falls back to the name-based emblem. Each entry can hold
additional metadata alongside `logo` as needed.

## Get social auth working

If you want to test/develop social auth via Github you need to set the following 2 environment variables in your `config/.env` file:

```
export AUTH_ID_GITHUB="someid"
export AUTH_SECRET_GITHUB="somesecret"
```

You can get these tokens via creating a Github OAuth App on https://github.com/settings/developers

## Cleaning up packages

If you want to clean up package data, you can run the following command:

```bash
ddev exec bin/cake clean
```

## Start with a fresh DB

If you want to start with a fresh DB, you can run the following command:

```bash
ddev exec -s db mysql -uroot -proot -e "DROP DATABASE IF EXISTS db; CREATE DATABASE db; CREATE DATABASE testdb;"
```

> [!NOTE]
> Don't forget to apply migrations again.

## Checking changes

Run the PHP suite with `ddev exec composer test`, code style with
`ddev exec composer cs-check`, and static analysis with `ddev exec composer phpstan`.

Run `npm run check` to build the frontend assets and run the browser suite.

For browser regressions, install dependencies with `npm ci`, build with
`npm run build`, and install Chromium with `npx playwright install chromium`.
Then run `npm run test:browser` with PHP 8.5 and SQLite available. The suite
starts a local PHP server using a separate `tmp/browser.sqlite` fixture database.

Interact owns Alpine startup. Application components register through
`alpine:init`; PHP islands live in `src/Island` with templates in
`templates/island`. Composer supplies `/interact/js/runtime.js`. The layout
renders the app module before Interact's script block so components register
before Alpine initializes.
