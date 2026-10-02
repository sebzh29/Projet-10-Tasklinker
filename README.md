# TaskLinker

TaskLinker est une application web de gestion de projets, de tâches et d’équipes, développée avec Symfony dans le cadre du projet 8 de la formation OpenClassrooms.

Elle permet de suivre l’avancement des projets et de répartir les tâches entre les employés.

## Fonctionnalités

### Gestion des projets

- Afficher la liste des projets actifs.
- Créer un projet.
- Modifier les informations d’un projet.
- Associer des employés à un projet.
- Archiver un projet.

### Gestion des tâches

- Créer une tâche au sein d’un projet.
- Modifier une tâche.
- Supprimer une tâche.
- Assigner une tâche à un employé appartenant au projet.
- Organiser les tâches selon leur statut :
  - To Do.
  - Doing.
  - Done.
- Définir une description et une échéance.

### Gestion des employés

- Afficher les employés et leurs informations.
- Modifier les informations d’un employé.
- Supprimer un employé.
- Retirer automatiquement l’employé supprimé de ses projets.
- Conserver ses tâches, qui deviennent non assignées.

Les employés sont créés à partir des fixtures : aucune fonctionnalité de création d’employé n’est proposée dans l’application.

## Technologies utilisées

- PHP 8.2 ou version ultérieure.
- Symfony 7.4.
- Doctrine ORM.
- MySQL.
- Twig.
- Symfony Forms.
- Doctrine Fixtures.
- Faker.
- HTML et CSS.
- Git et GitHub.

## Prérequis

Avant l’installation, les outils suivants doivent être disponibles :

- PHP 8.2 minimum.
- Composer.
- MySQL.
- Symfony CLI.
- Git.

## Installation

### 1. Cloner le projet

```bash
git clone URL_DU_DEPOT_GITHUB
cd Projet-8-Tasklinker
```

Remplacer `URL_DU_DEPOT_GITHUB` par l’adresse réelle du dépôt.

### 2. Installer les dépendances

```bash
composer install
```

### 3. Configurer la base de données

Créer un fichier `.env.local` à la racine du projet :

```dotenv
DATABASE_URL="mysql://root:root@127.0.0.1:3306/tasklinker-symf?serverVersion=8.0.32&charset=utf8mb4"
```

Adapter l’identifiant, le mot de passe, le nom de la base et la version de MySQL à l’environnement utilisé.

### 4. Créer la base de données

```bash
php bin/console doctrine:database:create
```

### 5. Exécuter les migrations

```bash
php bin/console doctrine:migrations:migrate
```

### 6. Charger les données de démonstration

```bash
php bin/console doctrine:fixtures:load
```

Attention : cette commande supprime les données existantes avant de charger les fixtures.

### 7. Démarrer le serveur

```bash
symfony serve
```

L’application est ensuite accessible à l’adresse indiquée dans le terminal, généralement :

```text
http://127.0.0.1:8000
```

## Modèle de données

L’application repose sur trois entités principales :

- `Projet` : nom, état d’archivage, employés associés et tâches.
- `Employe` : nom, prénom, email, date d’entrée et statut.
- `Tache` : nom, description, échéance, statut, projet et employé assigné.

### Statuts des employés

- CDI.
- CDD.
- Freelance.
- Stage.

### Statuts des tâches

- To Do.
- Doing.
- Done.

## Sécurité

Les actions sensibles, notamment la suppression des tâches, la suppression des employés et l’archivage des projets, utilisent des formulaires `POST` protégés par des jetons CSRF.

## Prototype

L’interface est basée sur le prototype fourni par OpenClassrooms :

https://github.com/OpenClassrooms-Student-Center/876-p8-m1-starterkit

## Auteur

Sébastien Glippa.
