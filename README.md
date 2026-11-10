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

## Featured plugin logos

Set an optional logo URL or image path alongside the package name in
`Packages.featured` in `config/app.php`:

```php
'featured' => [
    'cakedc/users' => '/img/plugins/users.svg',
    'vendor/plugin' => 'https://example.com/plugin-logo.png',
    'friendsofcake/crud' => null,
],
```

A null or broken logo falls back to the name-based emblem. Plain package-name
list entries also use the emblem.

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
