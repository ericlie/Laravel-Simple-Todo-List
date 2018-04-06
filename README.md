# Laravel-Simple-Todo-List

A simple todo list project built in Laravel 5.6
This project is great for beginner who wants to learn laravel

## Table of Contents

- [Installation](#installation)
- [Setting Up](#setting-up)

### Installation

Minimum Requirement

- PHP > 7.1
- MySQL 5.7

Git clone the repo

```sh
git clone https://github.com/ericlie/Laravel-Simple-Todo-List.git
```

Install Project dependencies

```sh
composer install
```

### Setting up

Set up .env file

```sh
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=YOUR_DATABASE_NAME
DB_USERNAME=YOUR_DATABASE_USERNAME
DB_PASSWORD=YOUR_DATABASE_PASSWORD
```

**Note!**: Please make sure you have create a database before running migration.

Run migration

```sh
php artisan migrate
```

Serve your project using artisan command

```sh
php artisan serve
```
