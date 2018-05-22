# LARAVEL MPESA API (DARAJA)

This is a wrapper implementation from this package https://github.com/Kabangi/mpesa

## Installation (Tested on 5.2)
1) In order to install mpesalaravel, just add the following to your composer.json. Then run `composer update`:

```json
"kabangi/mpesa-laravel": "^1.0.4",
```

2) Open your `config/app.php` and add the following to the `providers` array:

```php
Kabangi\MpesaLaravel\MpesaServiceProvider::class,
```

3) In the same `config/app.php` and add the following to the `aliases ` array: 

```php
'MPESA'   => Kabangi\MpesaLaravel\Facades\Mpesa::class,
```
4) Run the command below to publish the package config file `config/mpesa.php`:

```shell
php artisan vendor:publish
```
