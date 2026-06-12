# wixiweb-laravel

Package pour configurer simplement une application laravel avec nos bonnes pratiques.

## Installation

```shell
composer require wixiweb/wixiweb-laravel
```

## Publier le fichier de configuration

```shell
php artisan vendor:publish --tag=wixiweb-config
```

## Fonctionnalités

### Redirection des envois de mail

`APP_MAIL_TO` et `APP_MAIL_BCC` sont les variables d'environnement à configurer. Les deux variables prennent une liste d'adresses mail séparées par des virgules.

Il est possible de mettre des adresses mail dans la variable `APP_MAIL_WHITELIST`, les mails pour ces adresses ne seront pas redirigés.

### Gestion de tag pour mailpit

Il faut simplement mettre vos tags dans la variable d'environnement dans `APP_MAIL_TAGS`.

### Possibilité d'envoyer des exceptions par mail

Toutes les exceptions qui implémentent l'interface `\Wixiweb\WixiwebLaravel\Exceptions\MailableException` seront envoyées par mail aux addresses fournies dans la variable d'environnement `LOG_MAIL_RECIPIENTS`.

### Filtrage du contexte des logs

Le package ajoute automatiquement le contexte de la requête (`HTTP`), de l'utilisateur (`AUTH`), de la commande artisan (`CLI`), etc. à chaque log. Ces données peuvent contenir des informations sensibles (mots de passe, jetons, clés d'API…).

Ce filtrage est appliqué automatiquement à **deux endroits** :

- **Les logs fichiers** : via un `ContextLogProcessor` qui remplace celui de Laravel.
- **Les mails d'exception** : le contexte joint au mail est filtré avant l'envoi.

Aucune intervention n'est nécessaire : le filtrage est actif dès l'installation du package.

#### Masquer des champs (`hidden_fields`)

La configuration se fait dans le fichier `config/wixiweb.php`, sous la clé `logging.context.hidden_fields`. Le filtrage utilise la **dot-notation sur un chemin exact** : seule la valeur située exactement à ce chemin est masquée (remplacée par `***`).

```php
// config/wixiweb.php
'logging' => [
    // ...
    'context' => [
        'hidden_fields' => [
            'HTTP.POST.password',
            'HTTP.POST.password_confirmation',
            'HTTP.POST.current_password',
            'HTTP.POST._token',
            'HTTP.GET.password',
            'HTTP.GET.password_confirmation',
            'HTTP.GET.current_password',
        ],
        'filters' => [],
    ],
],
```

Points importants :

- Le chemin est **exact** : `HTTP.POST.password` masque uniquement cette position, et **pas** `HTTP.POST.profile.password` par exemple.
- Une valeur « vide » (`null`, `''`, `0`, `false`) n'est pas masquée.
- Pour masquer un champ ailleurs dans le contexte, ajoutez simplement son chemin complet (ex. `APP.api_key`).

#### Ajouter des filtres personnalisés (`filters`)

Pour transformer le contexte au-delà du simple masquage (ajout, suppression, anonymisation…), créez une classe implémentant `\Wixiweb\WixiwebLaravel\Logging\ContextFilterInterface` et déclarez-la dans `logging.context.filters`.

```php
namespace App\Filters;

use Wixiweb\WixiwebLaravel\Logging\ContextFilterInterface;

class AppendRequestIdFilter implements ContextFilterInterface
{
    public function filter(array $context): array
    {
        $context['request_id'] = request()->header('X-Request-Id');

        return $context;
    }
}
```

```php
// config/wixiweb.php
'logging' => [
    // ...
    'context' => [
        'hidden_fields' => [/* ... */],
        'filters' => [
            \App\Filters\AppendRequestIdFilter::class,
        ],
    ],
],
```

Les filtres sont exécutés **après** le masquage des `hidden_fields`, dans l'ordre de déclaration. Ils s'appliquent à la fois aux logs fichiers et aux mails d'exception.

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

Il est possible de spécifier des classes d'exception ou interfaces qui seront également envoyées par mail dans le fichier de configuration.

**Le reste des fonctionnalités sont gérées automatiquement via la classe `\Wixiweb\WixiwebLaravel\WixiwebServiceProvider`.**

### Fonctions helper

#### trans_plural

Cette fonction simplifie la traduction des formes singulier/pluriel. Utilise [trans_choice()](https://laravel.com/docs/localization#pluralization).

```php
trans_plural(string $singular, string $plural, int $count, array $replace = [], $locale = null) : string
```

Exemples d'utilisation :
```php
// Forme de base
trans_plural('article', 'articles', 1); // Retourne 'article'
trans_plural('article', 'articles', 2); // Retourne 'articles'

// Avec le compteur
trans_plural('article :count', 'articles :count', 1); // Retourne 'article 1'
trans_plural('article :count', 'articles :count', 2); // Retourne 'articles 2'

// Avec des variables personnalisées
trans_plural('article de :name', 'articles de :name', 1, ['name' => 'Jean']); // Retourne 'article de Jean'
```

#### trans_plural_map

Cette fonction permet des formes plurielles plus complexes en acceptant un tableau de chaînes associées à des compteurs spécifiques. Utilise [trans_choice()](https://laravel.com/docs/localization#pluralization).

```php
trans_plural_map(array $strings, int $count, array $replace = [], $locale = null) : string
```

Exemples d'utilisation :
```php
// Forme de base
trans_plural_map([
    '0,1' => 'article',
    '2,*' => 'articles',
], 1); // Retourne 'article'

// Avec le compteur
trans_plural_map([
    '0,1' => 'article :count',
    '2,*' => 'articles :count',
], 2); // Retourne 'articles 2'

// Avec des variables personnalisées
trans_plural_map([
    '0,1' => 'article de :name',
    '2,*' => 'articles de :name',
], 1, ['name' => 'Jean']); // Retourne 'article de Jean'

// Cas plus complexes
trans_plural_map([
    '0' => 'Aucun article',
    '1' => 'Un article',
    '2,3,4' => ':count articles',
    '5,*' => 'Beaucoup d\'articles (:count)',
], 3); // Retourne '3 articles'
```

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
