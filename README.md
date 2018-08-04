[![Build Status](https://travis-ci.com/siefkenj/tafs.svg?branch=master)](https://travis-ci.com/siefkenj/tafs)


# tafs
TA Feedback System

## Initial Set Up

```
npm install
```

## Compiling and Running Dev Server
In development mode, code is compiled and run on localhost port 3000.

```
npm start
```

Note: This starts a php server on port 3000.

## Database Setup

#### Development setup with test data
Login to database via Mariadb

Once logged on, do the following to create your local database and user to allow access via php:

Import Setup scripts for database setup, user setup and data population.
```
MariaDB[(none)]> source "/absolute/path/to/.../db/test_db_setup.sql"
MariaDB[(none)]> source "/absolute/path/to/.../db/schema.sql"
MariaDB[(none)]> source "/absolute/path/to/.../db/test_data.sql"
```

#### Production setup with empty database

1. Set database name, username, password, and database server variables in both `db/config.php` for database connection.
Example:
```
$database = getenv('TAFS_DB')?: "t_tafs";
$servername = getenv('TAFS_DB_SERVER')?: "localhost";
$username = getenv('TAFS_DB_USER')?: "myuser";
$password = getenv('TAFS_DB_PASSWORD')?: "mypassword";
```
2. User Setup:

Login to database via Mariadb

Once logged on, do the following to setup a user for your database:

Example (used with setup from previous step):
```
MariaDB[none]> CREATE USER 'myuser' IDENTIFIED BY 'mypassword';
```

```
MariaDB[none]> GRANT USAGE ON t_tafs.* TO 'myuser'@localhost IDENTIFIED BY 'mypassword';
```

```
MariaDB[none]> FLUSH PRIVILEGES;
```


3. Set database name based on what was set in the last two steps:
Example, Change first two lines of `db/schema.sql` to:
```
CREATE DATABASE IF NOT EXISTS `t_tafs` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `t_tafs`;
```

Then in MariaDB, import schema:
```
MariaDB[(none)]> source "/absolute/path/to/.../db/schema.sql"
```

4. (OPTIONAL) If wanting to populate the database with sample data, set database name in `db/test_data.sql` to the same one set in `db/config.php` variables (used with setup from previous step).

Change first two lines of `db/test_data.sql` to:
```
CREATE DATABASE IF NOT EXISTS `t_tafs` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `t_tafs`;
```

Then in MariaDB, import sample data:
```
MariaDB[(none)]> source "/absolute/path/to/.../db/test_data.sql"
```

## Prettier Tests
All .vue and .php files must be run through [prettier](https://prettier.io/) before submitting any PRs.

To install prettier with php plugin:
```
npm install -g prettier@1.12.1 prettier/plugin-php
```

To run prettier on files:
```
prettier --write --tab-width 4 --no-config file_name
```

## Mocha testing

To run tests, execute

```
$ npm test
```
