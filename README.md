# Manage Zoho Campaigns from Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/keepsuit/laravel-zoho-campaigns.svg?style=flat-square)](https://packagist.org/packages/keepsuit/laravel-zoho-campaigns)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/keepsuit/laravel-zoho-campaigns/run-tests?label=tests)](https://github.com/keepsuit/laravel-zoho-campaigns/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/keepsuit/laravel-zoho-campaigns/Fix%20PHP%20code%20style%20issues?label=code%20style)](https://github.com/keepsuit/laravel-zoho-campaigns/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/keepsuit/laravel-zoho-campaigns.svg?style=flat-square)](https://packagist.org/packages/keepsuit/laravel-zoho-campaigns)

This package provides an easy way to interact with the Zoho Campaigns API.

Right now only the following features are supported:

- Subscribe a contact to a list
- Unsubscribe a contact from a list

## Installation

You can install the package via composer:

```bash
composer require keepsuit/laravel-zoho-campaigns
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="campaigns-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="campaigns-config"
```

This is the contents of the published config file:

```php
return [
    /*
     * The driver to use to interact with Zoho Campaigns API.
     * You may use "log" or "null" to prevent calling the
     * API directly from your environment.
     */
    'driver' => env('CAMPAIGNS_DRIVER', 'api'),

    /**
     * Zoho datacenter region to use.
     * Available regions: us, eu, in, au, jp, cn
     */
    'region' => env('CAMPAIGNS_REGION'),

    /**
     * Zoho api client.
     * Run php artisan campaigns:setup and follow the instructions to generate an api client.
     */
    'client_id' => env('CAMPAIGNS_CLIENT_ID'),
    'client_secret' => env('CAMPAIGNS_CLIENT_SECRET'),

    /*
     * The listName to use when no listName has been specified in a method.
     */
    'defaultListName' => 'subscribers',

    /*
     * Here you can define properties of the lists.
     */
    'lists' => [

        /*
         * This key is used to identify this list. It can be used
         * as the listName parameter provided in the various methods.
         *
         * You can set it to any string you want and you can add
         * as many lists as you want.
         */
        'subscribers' => [

            /*
             * A Zoho campaigns list key.
             * https://www.zoho.com/campaigns/help/developers/list-management.html
             * You can find this value from Zoho campaigns dashboard under:
             * Contacts > Manage Lists > "Your list" > Setup
             */
            'listKey' => env('CAMPAIGNS_LIST_KEY'),

        ],
    ],
];
```

## First time setup:

This should be done also on production because tokens are saved in the database.
Run the following command and follow the instructions:

```bash
php artisan campaigns:setup
````

## Usage

### Subscribe a contact to a list

```php
use Keepsuit\Campaigns\Facades\Campaigns;

Campaigns::subscribe('user_a@example.com');

// with additional details: 
Campaigns::subscribe('user_a@example.com', [
    'First Name' => 'John',
    'Last Name' => 'Doe',
]);

// on a specific list:
Campaigns::subscribe('user_a@example.com', [], 'listName');
```

### Unsubscribe a contact from a list

```php
use Keepsuit\Campaigns\Facades\Campaigns;

Campaigns::unsubscribe('user_a@example.com');

// from a specific list:
Campaigns::unsubscribe('user_a@example.com', 'listName');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Fabio Capucci](https://github.com/cappuc)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
