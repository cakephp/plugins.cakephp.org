# plugins.cakephp.org

This repository contains the code responsible for https://plugins.cakephp.org

## Starting local development

You need [DDEV](https://docs.ddev.com/en/stable/) installed and configured on your machine.

After that start the app via `ddev start`

Next you need to apply migrations to create the database tables:

```bash
ddev exec bin/cake migrations migrate
ddev exec bin/cake migrations migrate -p Tags
```

After that you can perform a fresh sync via

```bash
ddev exec bin/cake sync_packages
```

With that you should now see what is currently on the production site.

## Cleaning up tables

If you want to clean up package data, you can run the following command:

```bash
ddev exec bin/cake clean
```

## Start with a fresh DB

If you want to start with a fresh DB, you can run the following command:

```bash
ddev exec -s db mysql -uroot -proot -e "DROP DATABASE IF EXISTS db; CREATE DATABASE db;"
```

> [!NOTE]
> Don't forget to apply migrations again.
