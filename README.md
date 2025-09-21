# CakePHP Application Skeleton

![Build Status](https://github.com/cakephp/app/actions/workflows/ci.yml/badge.svg?branch=5.x)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg?style=flat-square)](https://github.com/phpstan/phpstan)

An application skeleton for creating applications with [CakePHP](https://cakephp.org) 5.x. and [tailwind](https://tailwindcss.com)

The framework source code can be found here: [cakephp/cakephp](https://github.com/cakephp/cakephp).

## Requierements

1. PHP >= 8.1
2. NodeJS ^20.19.0 || >=22.12.0
3. NPM >=8.0.0

## Installation

1. Download [Composer](https://getcomposer.org/doc/00-intro.md) or update `composer self-update`.
2. Run `php composer.phar create-project --prefer-dist cakephp/app-tailwind [app_name]`.

If Composer is installed globally, run

```bash
composer create-project --prefer-dist cakephp/app-tailwind myapp
```

To use the latest commit:

```bash
composer create-project cakephp/app-tailwind tailwind -s dev
```

Next, install [tailwind](https://tailwindcss.com/docs) and [vite](https://vite.dev/guide/)
with npm:

```bash
npm install
```

## Running a development server

You can run both a PHP development server, and tailwind with on-demand rebuilds using:

```bash
bin/cake devserver
```

Then visit `http://localhost:8765` to see the welcome page. If you already have
a webserver that supports PHP, you can run the tailwind compiler on its own
with:

```bash
npm run dev
```

## Building for production

To build CSS assets for production, use:

```bash
npm run build
```

## Bake templates

This application skeleton contains a [bake
templates](https://book.cakephp.org/bake/3/en/development.html#application-bake-templates)
and helper templates that produce HTML using tailwind utility classes. You can
and should adapt these templates to fit the needs of your application.

## Demo app

Check out the [5.x-demo branch](https://github.com/cakephp/app-tailwind/tree/5.x-demo), which contains demo migrations and a seeder.
See the [README](https://github.com/cakephp/app-tailwind/blob/5.x-demo/README.md) on how to get it running.

## Update

Since this skeleton is a starting point for your application and various files
would have been modified as per your needs, there isn't a way to provide
automated upgrades, so you have to do any updates manually.

## Configuration

Read and edit the environment specific `config/app_local.php` and set up the
`'Datasources'` and any other configuration relevant for your application.
Other environment agnostic settings can be changed in `config/app.php`.
