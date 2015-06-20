# Laxative

A [Codeception](http://codeception.com/) extension to easily manage dump files from the [Db](http://codeception.com/docs/modules/Db) Module.

## What It Does

Laxative makes managing the dump file from the Codeception Db module a snap. It's particularly useful for teams with rapidly changing data models. Laxative eliminates the need for developers maintaining the dump file by always starting from scratch. Laxative will use the developers local codebase to create a fresh database for testing.

## Minimum Requirements

- Codeception 1.6.4
- PHP 5.4

## Installation using [Composer](https://getcomposer.org)

```bash
$ composer require jonpitch/laxative
```

Be sure to enable the extension in `codeception.yml` as shown in
[configuration](#configuration) below.

## Configuration

All enabling and configuration is done in `codeception.yml`.

### Enabling Laxative

```yaml
extensions:
    enabled:
        - Codeception\Extension\Laxative
    config:
      Codeception\Extension\Laxative:
        backup: true|false
        backup_path: 'path/to/your/backup/file.backup'
        host: 'database host IP|URL'
        database: 'database-name'
        login: 'database-login'
        migrations: 'your migrate command'
        seed: 'your seed command'
```

### Create An Empty Database

Laxative needs a consistent point to start from. The easiest way to do this is to leverage the Codeception Db module to do this for us.

* Create an empty database
* Create a dump file, for example: `pg_dump -h 192.168.10.10 -d my-database -U my-user`
* Configure your Codeception Db module to use this empty database

### Available options

#### Basic

- `backup: {backup}`
    - Enable backup and restore your database before and after a suite runs.
    - Default: false
- `backup_path: {backup_path}`
    - Relative path to store your database backup.
    - Default: `tests/_data/local.backup`
- `host: {host}`
    - The location of your database.
    - Example: '192.168.1.10'
- `database: {database}`
    - The name of your database.
- `login: {login}`
    - The database user login.
- `migrations: {migrations}`
    - A command to execute database migrations.
    - Example: 'php artisan migrate'
- `seed: {seed}`
    - A command to populate your database with data.
    - Example: 'php artisan db:seed'

## Usage

Once installed and enabled, before and after any suite with the Db module enabled, Laxative will:

* Backup your local database (if enabled)
* Use Codeception Db to restore from your empty database (see above)
* Run your migrations command
* Run your seed command
* Re-configure your Db module to use the fresh dump file.

Your tests will then run as normal from a fresh database. If you have the `backup` option enabled, when the suite is finished your database will be restored to how it was before the tests.

## Note(s)

* For now, Laxative is specific to Postgres. If you require something different, issue a pull request or send me an email.
