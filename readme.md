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

### Ajoute une classe de réponse pour les gates et policies

Voir documentation officielle => https://laravel.com/docs/12.x/authorization#policy-responses

La classe surcharge celle de laravel pour permettre de mettre un message non technique affichable aux utilisateurs.

```php
 Response::deny(message: 'Ceci est un message technique', humanReadableMessage: 'Ceci est un message pour être vu par les utilisateurs.');
 Response::denyWithStatus(400, message: 'Ceci est un message technique', humanReadableMessage: 'Ceci est un message pour être vu par les utilisateurs.');
 Response::denyAsNotFound(message: 'Ceci est un message technique', humanReadableMessage: 'Ceci est un message pour être vu par les utilisateurs.');
 Response::allow(message: 'Ceci est un message technique', humanReadableMessage: 'Ceci est un message pour être vu par les utilisateurs.');
```

Pour afficher le message :

```php
$response = Gate::inspect('test');

echo $response->humanReadableMessage();
```

### Ajoute un middleware pour faire de l'authentification HTTP basic

Pour configurer les identifiants il faut configurer deux variables d'environnement `APP_BASIC_AUTH_USERNAME` et `APP_BASIC_AUTH_PASSWORD`.

```php
// Protéger des routes
Route::middleware(BasicHttpAuthMiddleware::class)->group(static function () {
   Route::get('/ma-route-protegee',  [AuthController::class, 'maRouteProtegee'])->name('maRouteProtegee');
});
```

## Utilisation dans vos projets

Dans le fichier `bootstrap/app.php` ajouter :

```php
->withExceptions(function (Exceptions $exceptions) {
    Wixiweb::configureExceptionHandler($exceptions); // ← ligne à ajouter
})
```

Il est possible de spécifier des classes d'exception ou interfaces qui seront également envoyées par mail.

```php
->withExceptions(function (Exceptions $exceptions) {
    Wixiweb::configureExceptionHandler(
        $exceptions,
        [
            InvalidArgumentException::class,
            CustomMailableExceptionInterface::class
        ]
    ); // ← ligne à ajouter
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
