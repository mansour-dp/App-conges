# App Congés

## Description
App Congés est une application web complète pour la gestion des congés des employés. Elle inclut un frontend développé avec Vue.js et un backend basé sur Laravel.

## Fonctionnalités principales
- Gestion des utilisateurs (ajout, modification, suppression, activation/désactivation)
- Gestion des rôles et départements
- Gestion des demandes de congés
- Tableaux de bord pour différents types d'utilisateurs (Admin, Employé, etc.)
- Notifications en temps réel

## Structure du projet

```
APP/
├── Client/                # Frontend Vue.js
│   ├── src/
│   │   ├── components/    # Composants Vue
│   │   ├── views/         # Vues principales
│   │   ├── stores/        # Pinia stores
│   │   ├── router/        # Configuration des routes
│   │   └── services/      # Services API
│   ├── public/            # Fichiers statiques
│   └── package.json       # Dépendances frontend
├── Server/                # Backend Laravel
│   ├── app/
│   ├── routes/
│   ├── database/
│   ├── public/
│   └── composer.json      # Dépendances backend
├── docker/                # Configuration Docker
└── docker-compose.yml     # Orchestration Docker
```

## Prérequis
- Node.js (v16 ou supérieur)
- PHP (v8.1 ou supérieur)
- Composer
- Docker (optionnel pour le déploiement)

## Installation

### Frontend
1. Accédez au dossier `Client` :
   ```bash
   cd Client
   ```
2. Installez les dépendances :
   ```bash
   pnpm install
   ```
3. Lancez le serveur de développement :
   ```bash
   pnpm run dev
   ```

### Backend
1. Accédez au dossier `Server` :
   ```bash
   cd Server
   ```
2. Installez les dépendances :
   ```bash
   composer install
   ```
3. Configurez le fichier `.env` :
   ```bash
   cp .env.example .env
   ```
4. Générez la clé de l'application :
   ```bash
   php artisan key:generate
   ```
5. Lancez le serveur local :
   ```bash
   php artisan serve
   ```

## Déploiement avec Docker
1. Construisez et démarrez les conteneurs :
   ```bash
   docker-compose up --build
   ```
2. Accédez à l'application sur `http://localhost`.

## Tests

### Frontend
Lancez les tests unitaires :
```bash
pnpm run test
```

### Backend
Lancez les tests PHPUnit :
```bash
php artisan test
```

## Contribution
Les contributions sont les bienvenues ! Veuillez soumettre une pull request ou ouvrir une issue pour discuter des changements.

## Licence
Ce projet est sous licence MIT.
