# CesiZen

CesiZen est une application de gestion développée avec une architecture spécifique comprenant :
- **Backend** : API REST Symfony 7.2 avec authentification JWT
- **Frontend** : Application Vue.js 3 avec Capacitor pour le support mobile
- **Base de données** : MySQL

## 📋 Prérequis

Avant d'installer le projet, assurez-vous d'avoir :

### Backend (Symfony)
- PHP >= 8.2
- Composer
- MySQL >= 8.0
- OpenSSL (pour la génération des clés JWT)

### Frontend (Vue.js)
- Node.js >= 18
- npm ou yarn

## 🚀 Installation

### 1. Cloner le projet

```bash
git clone <url-du-repo>
cd CesiZen
```

### 2. Configuration du Backend (Symfony)

#### Installer les dépendances PHP
```bash
cd back
composer install
```

#### Configuration de l'environnement
```bash
# Copier le fichier d'environnement
cp .env .env.local

# Modifier .env.local avec vos paramètres de base de données
# DATABASE_URL="mysql://username:password@127.0.0.1:3306/cesizen?serverVersion=8.0.32&charset=utf8mb4"
```

#### Génération des clés JWT
```bash
# Générer les clés JWT pour l'authentification
php bin/console lexik:jwt:generate-keypair
```

#### Configuration de la base de données
```bash
# Créer la base de données
php bin/console doctrine:database:create

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Charger les fixtures (données de test)
php bin/console doctrine:fixtures:load
```

### 3. Configuration du Frontend (Vue.js)

#### Installer les dépendances Node.js
```bash
cd ../front
npm install
```

## 🏃‍♂️ Lancement du projet

### Démarrage du Backend
```bash
cd back
php bin/console server -d
```

### Démarrage du Frontend
```bash
cd front
npm run dev
```

L'application sera accessible à :
- **Frontend** : http://localhost:5173
- **Backend API** : http://localhost:8000
- **Documentation API** : http://localhost:8000/api/doc

## 🔧 Commandes utiles

### Backend (Symfony)

#### Base de données
```bash
# Réinitialiser la base de données
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load

# Créer une nouvelle migration
php bin/console make:migration

# Créer une nouvelle entité
php bin/console make:entity
```

#### Tests
```bash
# Lancer les tests
php bin/phpunit

# Tests avec couverture
php bin/phpunit --coverage-html coverage
```

#### Cache
```bash
# Vider le cache
php bin/console cache:clear

# Vider le cache de production
php bin/console cache:clear --env=prod
```

### Frontend (Vue.js)

#### Développement
```bash
# Mode développement avec hot-reload
npm run dev

# Build de production
npm run build

# Prévisualisation du build
npm run preview
```

## 📁 Structure du projet

```
CesiZen/
├── back/               # Backend Symfony
│   ├── src/           # Code source PHP
│   ├── config/        # Configuration Symfony
│   ├── migrations/    # Migrations de base de données
│   ├── public/        # Point d'entrée web
│   └── tests/         # Tests PHP
├── front/             # Frontend Vue.js
│   ├── src/           # Code source Vue.js
│   ├── public/        # Assets statiques
│   └── e2e/           # Tests end-to-end
└── docs/              # Documentation du projet
```

## 🛠️ Technologies utilisées

### Backend
- **Symfony 7.2** - Framework PHP
- **Doctrine ORM** - Mapping objet-relationnel
- **LexikJWTAuthenticationBundle** - Authentification JWT
- **NelmioApiDocBundle** - Documentation API
- **NelimioCorsBundle** - Gestion CORS

### Frontend
- **Vue.js 3** - Framework JavaScript progressif
- **Vite** - Build tool et serveur de développement
- **Capacitor** - Framework pour applications mobiles hybrides
- **Vitest** - Framework de tests unitaires
- **Playwright** - Tests end-to-end

## 📚 Documentation

- [Documentation Symfony](https://symfony.com/doc/current/index.html)
- [Documentation Vue.js](https://vuejs.org/guide/)

## 👥 Équipe

Développé par Esteban Racine dans le cadre du projet CESIZen.