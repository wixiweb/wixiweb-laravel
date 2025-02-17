# wixiweb-laravel

Package pour configurer simplement une application laravel avec nos bonnes pratiques.

## Installation

```shell
compsoer require wixiweb/wixiweb-laravel
```

## Utilisation

Dans le fichier `bootstrap/app.php` ajouter :

```php
->withExceptions(function (Exceptions $exceptions) {
    Wixiweb::configureExceptionHandler($exceptions); // <- ligne à ajouter
})
```

## Publier le fichier de configuration

```shell
php artisan vendor:publish --tag=wixiweb
```
