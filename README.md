# Laravel Vertical Slices Template
A laravel template for building efficient vertical slice applications

## Features
- custom module json manifests
- feature flag based interface method handling
- fully separate per-module logic
- built-in DTO handling with `spatie/laravel-data`

## Commands
- `php artisan make:module $moduleName` - fully scaffolds a module
- `php artisan module:make-model $module $modelName` - creates a model, factory and repository for a module
- `php artisan module:make-migration $module $migrationName` - creates a migration for the module
- `php artisan core:clear` - cleans all internal cache

## k8s
### Required packages
- k3s
- helm
- cilium-cli
