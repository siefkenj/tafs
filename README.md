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

Login to database via Mariadb

Once logged on, do the following to create your local database and user to allow access via php:

### Database Setup
Creating the database and import the schema:
```
MariaDB[(none)]> source "/absolute/path/to/.../db/schema.sql"
```

User Setup:
```
MariaDB[ta_feedback]> CREATE USER 'myuser' IDENTIFIED BY 'mypassword';
```

```
MariaDB[ta_feedback]> GRANT USAGE ON ta_feedback.* TO 'myuser'@localhost IDENTIFIED BY 'mypassword';
```

```
MariaDB[(none)]> FLUSH PRIVILEGES;
```

## Mocha testing

To run tests, execute

```
$ npm test
```
