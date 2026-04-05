# plugins.cakephp.org

This repository contains the code responsible for https://plugins.cakephp.org

## Starting local development

You need [DDEV](https://docs.ddev.com/en/stable/) installed and configured on your machine.

After that start the app via `ddev start`

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
ddev exec npm i
ddev exec bin/cake devserver
```

With that you should now see what is currently on the production site.

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
