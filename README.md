# Manage Zoho Campaigns from Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/keepsuit/laravel-zoho-campaigns.svg?style=flat-square)](https://packagist.org/packages/keepsuit/laravel-zoho-campaigns)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/keepsuit/laravel-zoho-campaigns/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/keepsuit/laravel-zoho-campaigns/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/keepsuit/laravel-zoho-campaigns/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/keepsuit/laravel-zoho-campaigns/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/keepsuit/laravel-zoho-campaigns.svg?style=flat-square)](https://packagist.org/packages/keepsuit/laravel-zoho-campaigns)

This package provides an easy way to interact with the Zoho Campaigns API.

Right now only the following features are supported:

- Subscribe a contact to a list
- Unsubscribe a contact from a list
- Get subscribers from a list
- Get subscribers count of a list

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
    /**
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

    /**
     * The listName to use when no listName has been specified in a method.
     */
    'defaultListName' => 'subscribers',

    /**
     * Here you can define properties of the lists.
     */
    'lists' => [

        /**
         * This key is used to identify this list. It can be used
         * as the listName parameter provided in the various methods.
         *
         * You can set it to any string you want and you can add
         * as many lists as you want.
         */
        'subscribers' => [

            /**
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
Campaigns::subscribe('user_a@example.com', contactInfo: [
    'First Name' => 'John',
    'Last Name' => 'Doe',
]);

// on a specific list:
Campaigns::subscribe('user_a@example.com', contactInfo: [], list: 'listName');

// on a specific list via list key
Campaigns::subscribe('user_a@example.com', contactInfo: [], list: '3z9d17e6b4f3a2c5d8a1bc9478df32561e3ab4d2c4fc7a5e9c0db8e34176ca92a0');

// if user previously unsubscribed from the list, you can resubscribe them (it support the same parameters as subscribe):
Campaigns::resubscribe('user_a@example.com');
```

### Unsubscribe a contact from a list

```php
use Keepsuit\Campaigns\Facades\Campaigns;

Campaigns::unsubscribe('user_a@example.com');

// from a specific list:
Campaigns::unsubscribe('user_a@example.com', list: 'listName');

// on a specific list via list key
Campaigns::unsubscribe('user_a@example.com', list: '3z9d17e6b4f3a2c5d8a1bc9478df32561e3ab4d2c4fc7a5e9c0db8e34176ca92a0');
```

### Get subscribers from a list

```php
use Keepsuit\Campaigns\Facades\Campaigns;

// This method returns a LazyCollection and will fetch additional pages when needed.
// You can filter by status and sort the results.
Campaigns::subscribers(status: 'active', sort: 'desc');

// from a specific list:
Campaigns::subscribers(list: 'listName');

// on a specific list via list key
Campaigns::subscribers(list: '3z9d17e6b4f3a2c5d8a1bc9478df32561e3ab4d2c4fc7a5e9c0db8e34176ca92a0');
```

### Get subscribers count of a list

```php
use Keepsuit\Campaigns\Facades\Campaigns;

// You can filter by status.
Campaigns::subscribersCount(status: 'active');

// from a specific list:
Campaigns::subscribersCount(list: 'listName');

// on a specific list via list key
Campaigns::subscribersCount(list: '3z9d17e6b4f3a2c5d8a1bc9478df32561e3ab4d2c4fc7a5e9c0db8e34176ca92a0');
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
