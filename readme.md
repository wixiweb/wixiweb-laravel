# wixiweb-laravel

Package pour configurer simplement une application laravel avec nos bonnes pratiques.

## Installation

```shell
composer require wixiweb/wixiweb-laravel
```

## Fonctionnalités

### Redirection des envois de mail et notification par mail

Pour les notifications, il faut que votre model `notifiable` utilise le trait `\Wixiweb\WixiwebLaravel\Notifications\ConfigurableNotifiable` et que votre notification utilise la classe `\Wixiweb\WixiwebLaravel\Notifications\ApplicationMailMessage` à la place de `\Illuminate\Notifications\Messages\MailMessage`.

Pour les mails en direct, c'est géré via les évènements donc rien à faire de particulier.
`APP_MAIL_TO` et `APP_MAIL_BCC` sont les variables d'environnement à configurer.

### Gestion de tag pour mailpit

Il faut simplement mettre vos tags dans la variable d'environnement dans `APP_MAIL_TAGS`. Pour les notifications, il faut utiliser la classe `\Wixiweb\WixiwebLaravel\Notifications\ApplicationMailMessage`.

### Possibilité d'envoyer des exceptions par mail

Toutes les exceptions qui implémentent l'interface `\Wixiweb\WixiwebLaravel\Exceptions\MailableException` seront envoyées par mail aux addresses fournies dans la variable d'environnement `LOG_MAIL_RECIPIENTS`.

### Models stricts

Voir https://laravel.com/docs/11.x/eloquent#configuring-eloquent-strictness. Configurable dans le fichier de config dans la clé `strict_model`. Strict par défaut.

### Prévention de l'exécution des commandes CLI destructives

Il est possible de désactiver les commandes destructives pour certains environnements. Configurable dans le fichier de config dans la clé `prohibit_destructive_commands_envs`. Par défaut désactive uniquement pour la production.

### Gestion des transactions orpheline pour le système de queue

Voir https://laravel.com/docs/11.x/queues#job-events. Rien à faire dans vos projets, c'est géré automatiquement.

## Utilisation dans vos projets

Dans le fichier `bootstrap/app.php` ajouter :

```php
->withExceptions(function (Exceptions $exceptions) {
    Wixiweb::configureExceptionHandler($exceptions); // ← ligne à ajouter
})
```

Dans vos notifications :

```php
class TestMailNotification extends Notification
{
    ...
    public function toMail($notifiable,): MailMessage
    {
        return (new ApplicationMailMessage()) // ← Utilisation de la classe ApplicationMailMessage. 
            ->line('');
    }
    ...
}
```

Dans vos models notifiable :

```php

class User extends Authenticatable
{
    use HasFactory, Notifiable, ConfigurableNotifiable; // ← Utilisation du trait ConfigurableNotifiable.
    ...
}
```

**Le reste des fonctionnalités sont gérées automatiquement via la classe `\Wixiweb\WixiwebLaravel\WixiwebServiceProvider`.**

## Publier le fichier de configuration

```shell
php artisan vendor:publish --tag=wixiweb
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
