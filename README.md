# Test DistriCall

Ce projet back-end est une API développé avec Symfony sur la gestion de tâche. Suivez les instructions ci-dessous pour configurer et exécuter l'application.

## ⚠️ Prérequis

- **PHP** : Assurez-vous d'avoir une version de PHP **supérieure à 8.2**.
- **Composer** : Gestionnaire de dépendances pour PHP.
- **Symfony CLI** : Assurez-vous que la CLI Symfony est installée. [Télécharger Symfony CLI](https://symfony.com/download)

## 🔧 Installation 

1. Ouvrez le fichier `.env`, remplacer la variable `DATABASE_URL`par votre base de données.

2. Ouvrez un terminal à la racine du projet (dans le fichier task) et exécuter les commandes suivantes : 
```env
 - composer install
 - php bin/console doctrine:database:create
 - php bin/console make:migration
 - php bin/console doctrine:migrations:migrate
 - symfony server:start
 ```
 Vous pouvez désormais utilisé les routes :
 ```env
 - GET : 
      http://127.0.0.1:8000/api/task?title={title} : rechercher par titre 
      http://127.0.0.1:8000/api/task?description={description} : rechercher par description
      http://127.0.0.1:8000/api/task/perPage : rechercher par page de 10 tâches
 - POST : 
      http://127.0.0.1:8000/api/task : ajouter une tâche
 - PUT : 
      http://127.0.0.1:8000/api/task/{id} : modifier une tâche selon son id
 - DELETE : 
      http://127.0.0.1:8000/api/task/{id} : supprimer une tâche selon son id
 ```
## ✔️ Test fonctionnels
1. Ouvrez le fichier `.env.test`, remplacer la variable `DATABASE_URL`par une base de données test.
2. Exécuter les commandes suivantes : 
 ```env
 - php bin/console doctrine:database:create --env=test
 - php bin/console doctrine:schema:update --force --env=test 
 - php bin/phpunit
 ```
 
 ## 💻 Choix techniques
- **ORM (Object-Relational Mapping)** : Utilisation de Doctrine pour gérer la communication   entre l'application et la base de données.

 - **Symfony Maker Bundle** : Facilite la création des classes, des contrôleurs, des entités, etc.

 - **Symfony Serializer Pack** : Convertit facilement les objets PHP en JSON pour une communication fluide avec le front-end.
