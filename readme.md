# Laravel Module SmartBread Gerenator 

Package for generating [Laravel Modules](https://github.com/nWidart/laravel-modules) from templates. 

This work is based on rewrite on a work of David Carr. 

See: 
dcblogdev/laravel-module-generator (https://github.com/dcblogdev/laravel-module-generator)

# Requirements

## Laravel
PHP 8.2+
Laravel 12
Laravel Modules 12 (https://github.com/nWidart/laravel-modules)

## Javascript
bootstrap 5.3
bootstrap-icons 1.11

# Install

You can install the package via composer:

```bash
composer require astradev/laravel-module-smartbread-generator
```

Publish both the `config` and `stubs`:

```bash
php artisan vendor:publish --provider="astradev\LaravelModuleSmartBreadGenerator\LaravelModuleSmartBreadGeneratorServiceProvider"
```

This will publish a `module-smartbread.php` config file

This contains:
```php
    'templates' => [
        'Livewire - Volt - Bread Web & API' => 'stubs/module-smartbread/livewire-volt-full',
    ],
    'ignore_files' => ['module.json'],
    'use_singular' => 'true',
    'routes_pathnames' => [ 'routes/api.php', 'routes/web.php' ]
```
By default, the stubs will be located at stubs/module-smartbread you can add your paths by adding folders and updating the config file.

# Parameters:
`temmplates`: templates to be used.
`use_singular`: avoid to use laravel singular / plural standarts.
`routes_pathnames`: neded to merge routes files.
`ignore_files`: ignore creation of files.

# Usage

```bash
php artisan module:smartbread
```
or 
```bash
php artisan module:build "Livewire - Volt - Bread Web & API" modulename modelname tablename 
```

`{template?}` is the name of the template you want to use. If you don't provide a name you will be asked to enter one.

`{module?}` is the name of a existent module. If you don't provide a name you will be asked to enter one.

`{model?}` is the name of a new model to be created. If you don't provide a name you will be asked to enter one.

`{table?}` is the table's name on the new model to be created. 

Then run:

```bash
composer dump-autoload
```
## Placeholders:

These placeholders are replaced with the name provided when running `php artisan module:smartbread`

`{Module}` = Module name ie `PurchaseOrders`

`{module}` = Module name in lowercase ie `purchaseOrder`

`{module_}` = module name with underscores ie `purchase_orders`

`{module-}` = module name with hyphens ie `purchase-orders`

`{module }` = module name puts space between capital letters ie `PurchaseOrders` becomes `Purchase Orders`

`{module_plural}` = Plural module name in lowercase ie demo becomes `demos`

`{Model}` = Model name ie `PurchaseOrder`

`{model}` = Model name in lowercase ie `purchaseOrder`

`{model_}` = model name with underscores ie `purchase_orders`

`{model-}` = model name with hyphens ie `purchase-orders`

`{model }` = model name puts space between capital letters ie `PurchaseOrder` becomes `Purchase Order`

`{model_plural}` = Plural module name in lowercase ie demo becomes `demos`

`{table}` = Table name in snakecase ie demo becomes `demos`

## Contributing

Contributions are welcome and will be fully credited.

Contributions are accepted via Pull Requests on [Github][4].

## Pull Requests

- **Document any change in behaviour** - Make sure the `readme.md` and any other relevant documentation are kept up-to-date.

- **Consider our release cycle** - We try to follow [SemVer v2.0.0][2]. Randomly breaking public APIs is not an option.

- **One pull request per feature** - If you want to do more than one thing, send multiple pull requests.

## Security

If you discover any security related issues, please email leandro@astradev.io instead of using the issue tracker.

## License

license. Please see the license file[3] for more information.

[1]:    changelog.md
[2]:    http://semver.org/
[3]:    license.md
