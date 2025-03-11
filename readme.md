# wixiweb-laravel

Package pour configurer simplement une application laravel avec nos bonnes pratiques.

## Installation

```shell
composer require wixiweb/wixiweb-laravel
```

## Publier le fichier de configuration

```shell
php artisan vendor:publish --tag=wixiweb
```

## Fonctionnalités

### Redirection des envois de mail

`APP_MAIL_TO` et `APP_MAIL_BCC` sont les variables d'environnement à configurer. Les deux variables prennent une liste d'adresses mail séparées par des virgules.

Il est possible de mettre des adresses mail dans la variable `APP_MAIL_WHITELIST`, les mails pour ces adresses ne seront pas redirigés.

### Gestion de tag pour mailpit

Il faut simplement mettre vos tags dans la variable d'environnement dans `APP_MAIL_TAGS`.

### Possibilité d'envoyer des exceptions par mail

Toutes les exceptions qui implémentent l'interface `\Wixiweb\WixiwebLaravel\Exceptions\MailableException` seront envoyées par mail aux addresses fournies dans la variable d'environnement `LOG_MAIL_RECIPIENTS`.

### Models stricts

Voir https://laravel.com/docs/11.x/eloquent#configuring-eloquent-strictness. Configurable dans le fichier de config dans la clé `strict_model`. Strict par défaut.

### Gestion des transactions orpheline pour le système de queue

Voir https://laravel.com/docs/11.x/queues#job-events. Rien à faire dans vos projets, c'est géré automatiquement.

### Ajoute une commande artisan `wixiweb:db:create {dbname?}`

Cette commande sert à créer une base de donnée si celle-ci n'existe pas. Par défaut prend la base configurée par défaut. Il est possible de passer en argument le nom de la base de donnée.

## Utilisation dans vos projets

Dans le fichier `bootstrap/app.php` ajouter :

```php
->withExceptions(function (Exceptions $exceptions) {
    Wixiweb::configureExceptionHandler($exceptions); // ← ligne à ajouter
})
```

**Le reste des fonctionnalités sont gérées automatiquement via la classe `\Wixiweb\WixiwebLaravel\WixiwebServiceProvider`.**

## Développement

1. Installer les dépendances
    ```shell
    docker run -v .:/app -w /app composer install
    ```
2. Jouer les tests
    ```shell
    chmod u+x ./run-tests.sh
   ./run-tests.sh
    ```
