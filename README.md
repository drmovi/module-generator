# Laravel Module Generator

A Laravel package for generating modular applications with a standardized structure.

## Installation

1. Add the package to your Laravel project:

```bash
composer require drmovi/laravel-module
```

2. The service provider will be automatically registered via Laravel's package auto-discovery.

## Usage

Generate a new module using the artisan command:

```bash
php artisan module:generate drmovi/module-name
```

This will create a new module in the `modules/module-name` directory with the following structure:

```
modules/module-name/
├── composer.json
├── config/
│   └── module-name.php
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── routes/
│   ├── api.php
│   └── web.php
├── src/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── ModuleNameController.php
│   │   └── Middleware/
│   ├── Models/
│   │   └── ModuleName.php
│   └── Providers/
│       └── ModuleNameServiceProvider.php
└── tests/
    ├── Feature/
    │   └── ModuleNameTest.php
    └── Unit/
        └── ModuleNameUnitTest.php
```

## Features

- Automatic namespace generation following PSR-4 standards
- Service provider with route, migration, and config registration
- Controller and model scaffolding
- Test structure with Feature and Unit test examples
- Automatic composer.json updates for path repositories
- Laravel auto-discovery support

## Module Structure

Each generated module includes:

- **Config**: Module configuration file
- **Database**: Migrations, factories, and seeders
- **Routes**: Web and API route definitions
- **Controllers**: HTTP controllers
- **Models**: Eloquent models
- **Service Provider**: Laravel service provider for module registration
- **Tests**: Feature and unit tests

## After Generation

After generating a module, run:

```bash
composer update
```

The module will be automatically registered and available in your Laravel application.
