# Bookstore Inventory Management System

A Laravel 12 MVC application for administrator sign-in, book inventory CRUD, filtered inventory reports, and PDF/XLSX exports.

## Setup

Requirements: PHP 8.2+, Composer, and SQLite (or another Laravel-supported database).

```sh
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

Configure the database and application settings in `.env` before running migrations. On macOS/Linux, use `cp .env.example .env` instead of `copy`.

Create the first administrator in Tinker; registration is intentionally not exposed:

```sh
php artisan tinker
```

```php
\App\Models\User::create([
    'name' => 'Bookstore Administrator',
    'email' => 'admin@example.com',
    'username' => 'admin',
    'password' => \Illuminate\Support\Facades\Hash::make('choose-a-strong-password'),
]);
```

Sign in at `/login` with either the email address or username. Authenticated administrators can manage books at `/books` and use reports at `/reports`. Each book belongs to its creator; book lists, details, updates, deletes, reports, and exports are scoped to the signed-in administrator.

## Features

- Server-validated book CRUD with category, availability, and non-negative PHP price validation.
- Combined category and availability report filters with a reset option.
- PDF and `.xlsx` exports generated from the same filtered records as the report page.
- CSRF-protected authentication and inventory forms; deletion requires browser confirmation.

## Tests

```sh
php artisan test
```
