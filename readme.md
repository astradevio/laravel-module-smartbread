# Laravel Module SmartBread 

Package for generating BREADs (Browse, Read, Edit, Add and Delete operations) on [Laravel Modules](https://github.com/nWidart/laravel-modules) from stub files.

## Requirements

## Laravel
PHP 8.2+
Laravel 12
Laravel Modules 12 (https://github.com/nWidart/laravel-modules)

Since version 0.1.0 default stubs files requires 'astradevio/smartform-module' package 
(https://git.astradev.io/astradevio/smartform-module)

## Install

You can install the package via composer:

```bash
composer require astradev/laravel-module-smartbread
```

To publish both the `config` and `stubs`:

```bash
php artisan vendor:publish --provider="astradevio\LaravelModuleSmartBread\SmartBreadServiceProvider" 
```
## Configuration 
This will publish a `smartbread.php` config file

This file contains:
```php
    'templates' => [
        'Livewire - Volt - Bread Web & API' => 'stubs/smartbread/livewire-volt-full',
    ],
    'ignore_files' => ['module.json'],
    'use_singular' => 'true',
    'routes_pathnames' => [ 'routes/api.php', 'routes/web.php' ]
```

By default, the stubs will be located at stubs/module-smartbread you can add your paths by adding folders and updating the config file.

###Parameters:###
```
`templates`: templates to be used.
`use_singular`: avoid to use laravel singular / plural standarts.
`routes_pathnames`: neded to merge routes files.
`ignore_files`: ignore creation of files.
```

## Usage

```bash
php artisan smartbread:generate     : gerenate bread template from stubs.
php artisan smartbread:replace view : update (overwrite) existent views fom a original stubs.
```

Afer running `generate` do a:

```composer dump-autoload```

## Path replacements

On stubs directory, stubs file names will be replaced with the following convention.

```
{template?} : is the name of the template you want to use. If you don't provide a name you will be asked to enter one.
{module?} : is the name of a existent module. If you don't provide a name you will be asked to enter one.
{model?}  : is the name of a new model to be created. If you don't provide a name you will be asked to enter one.
{table?}  : is the table's name on the new model to be created. 
```

## File content replacements

On stubs directory, stubs file content will be replaced with the following convention.

```
{Module} = Module name in PascalCase ie 'ModuleName'.
{module} = Module name in camelCase 'moduleName'.
{module_} = Module name in snake-case with underscores ie 'module_name'.
{module-} = Module name in snake-case with hyphens ie 'module-name'.
{module } = Module name puts space between capital letters ie becomes 'Module Name'.
{module_plural} = Plural module name in PascalCase ie 'ModuleNames'.

{Model} = Model name in Pascal Case ie 'PurchaseOrder'.
{model} = Model name in camelCase ie 'purchaseOrder'.
{model_} = Model name in snake-case with underscores ie 'purchase_order'.
{model-} = model name in snake-case with hyphens ie 'purchase-order'.
{model } = model name puts space between capital letters ie 'PurchaseOrder' becomes 'Purchase Order'.
{model_plural} = Plural module name in PascalCase ie `PurchaseOrders'.

{table} = Table name in snake_case with underscores ie demo-file becomes 'demo_file' or 'demo-files' becomes 'demo_files'.
```

### Config parameters

```
'ignore-files': ignores overwrite of listed files.
'use-singular': avoids plural effects.
'append_module_to_tablename': appends {module_} _ to {table} varable.
```

## Thanks to:

This work is based on ideas and work of David Carr. 

See: 
dcblogdev/laravel-module-generator (https://github.com/dcblogdev/laravel-module-generator)

