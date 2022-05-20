# Projet Le Blog de Batman

## Installation

```
git clone https://github.com/harlik-flo/batblog.git
```

### Modifier les paramètre d'environement dans le fichier . env pour les faire correspondre à votre environnement (Accès base de données, clés Google, Recaptcha, ect...)


```
# Accès base de données à modifier

# Clés Google Recaptcha à modifier

```

### Déplacer le terminal dans le dossier

```
cd badblog
```

### Taper les commandes suivantes :
```
composer install
symfony console doctrine:database:create
symfony console make:migration
symfony console doctrine:migration:migrate
symfony console doctrine:fixtures:load
symfony console assets:install public
```


les fixtures crééront :
* Un compte admin (email : Admin@a.a, mot de passe : 123456.Admin )
* 10 comptes utilisateurs (email : aléatoire, mot de passe :123456.Admin )
* 200 articles
* Entre 0 et 10 commentaires par article

### Démarrer le serveur Symfony :
```
symfony serve
```