# 🍽️ Restomate API

> API REST de réservation de tables de restaurant — Développée avec Laravel 11 & documentée avec Swagger (OpenAPI 3.0)

![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Swagger](https://img.shields.io/badge/Swagger-OpenAPI%203.0-85EA2D?style=for-the-badge&logo=swagger&logoColor=black)

---

## 📋 Table des matières

- [À propos](#-à-propos)
- [Fonctionnalités](#-fonctionnalités)
- [Architecture](#-architecture)
- [Prérequis](#-prérequis)
- [Installation](#-installation)
- [Documentation API](#-documentation-api)
- [Endpoints](#-endpoints)
- [Authentification](#-authentification)
- [Rôles & Permissions](#-rôles--permissions)
- [Auteur](#-auteur)

---

## 📖 À propos

**Restomate** est une API backend complète permettant de gérer des réservations de tables dans des restaurants. Elle couvre l'ensemble du cycle de vie d'une réservation — de la recherche de disponibilité jusqu'à la confirmation ou l'annulation.

Ce projet a été développé dans le cadre d'un concours de développement backend organisé suite à une masterclass sur le thème : *"Du besoin métier à l'architecture : penser données avant le code pour un back-end solide."*

---

## ✨ Fonctionnalités

- ✅ **Authentification** par token via Laravel Sanctum
- ✅ **Gestion des rôles** : Client & Administrateur
- ✅ **CRUD Restaurants** avec recherche et filtrage par ville
- ✅ **CRUD Tables** avec gestion de la capacité et localisation
- ✅ **Disponibilité en temps réel** des tables selon date, heure et nombre de convives
- ✅ **Réservations complètes** : création, modification, annulation, confirmation, no-show
- ✅ **Historique** des réservations par client
- ✅ **Pagination** sur tous les endpoints de liste
- ✅ **Recherche & filtrage** multi-critères
- ✅ **Archivage doux** (SoftDeletes) sur restaurants, tables et réservations
- ✅ **Statistiques** globales pour l'administrateur
- ✅ **Documentation interactive** Swagger / OpenAPI 3.0

---

## 🏗️ Architecture

```
Monolithique Modulaire — Laravel 13
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/     # Controllers par module
│   │   ├── Requests/            # Validation des données entrantes
│   │   └── Middleware/          # Filtres de sécurité
│   ├── Models/                  # Entités Eloquent
│   ├── Services/                # Logique métier (disponibilité)
│   └── Traits/                  # Réponses JSON standardisées
├── database/
│   ├── migrations/              # Structure des tables BDD
│   └── seeders/                 # Données de test
└── routes/
    └── api.php                  # Toutes les routes API versionnées /v1
```

### Entités principales

```
User ──────────────── Reservation
  (client/admin)           │
                           │
Restaurant ──── Table ─────┘
```

---

## 🔧 Prérequis

- PHP >= 8.2
- Composer >= 2.x
- MySQL >= 8.0
- Laravel Herd (recommandé) ou `php artisan serve`

---

## 🚀 Installation

### 1. Cloner le projet

```bash
git clone https://github.com/Alex-Yessougnon-AGO/restomate-api.git
cd restomate-api
```

### 2. Installer les dépendances

```bash
composer install
```

### 3. Configurer l'environnement

```bash
cp .env.example .env
php artisan key:generate
```

Modifier `.env` avec vos identifiants MySQL :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=restomate_db
DB_USERNAME=root
DB_PASSWORD=votre_mot_de_passe
```

### 4. Créer la base de données

```sql
CREATE DATABASE restomate_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Lancer les migrations et le seeder

```bash
php artisan migrate --seed
```

Cela crée toutes les tables et injecte des données de test :

| Compte | Email | Mot de passe | Rôle |
|--------|-------|-------------|------|
| Admin Restomate | admin@restomate.com | password123 | admin |
| Alex AGO | alex@restomate.com | password123 | client |

### 6. Générer la documentation Swagger

```bash
php artisan l5-swagger:generate
```

### 7. Démarrer le serveur

```bash
# Avec Laravel Herd → http://restomate-api.test
# OU
php artisan serve → http://localhost:8000
```

---

## 📚 Documentation API

La documentation interactive est accessible à :

```
http://restomate-api.test/api/documentation
```

ou

```
http://localhost:8000/api/documentation
```

---

## 🛣️ Endpoints

### 🔓 Publics (sans authentification)

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| `POST` | `/api/v1/auth/register` | Inscription client |
| `POST` | `/api/v1/auth/login` | Connexion |
| `GET` | `/api/v1/restaurants` | Liste des restaurants (filtre: city, search) |
| `GET` | `/api/v1/restaurants/{id}` | Détail d'un restaurant |
| `GET` | `/api/v1/restaurants/{id}/tables` | Tables d'un restaurant |
| `GET` | `/api/v1/restaurants/{id}/tables/available` | Tables disponibles (date, start_time, guests) |

### 🔐 Protégés (token requis)

#### Authentification
| Méthode | Endpoint | Description |
|---------|----------|-------------|
| `POST` | `/api/v1/auth/logout` | Déconnexion |
| `GET` | `/api/v1/auth/me` | Profil connecté |

#### Restaurants (Admin)
| Méthode | Endpoint | Description |
|---------|----------|-------------|
| `POST` | `/api/v1/restaurants` | Créer un restaurant |
| `PUT` | `/api/v1/restaurants/{id}` | Modifier un restaurant |
| `DELETE` | `/api/v1/restaurants/{id}` | Archiver un restaurant |

#### Tables (Admin)
| Méthode | Endpoint | Description |
|---------|----------|-------------|
| `POST` | `/api/v1/restaurants/{id}/tables` | Créer une table |
| `PUT` | `/api/v1/restaurants/{id}/tables/{tableId}` | Modifier une table |
| `DELETE` | `/api/v1/restaurants/{id}/tables/{tableId}` | Archiver une table |

#### Réservations
| Méthode | Endpoint | Rôle | Description |
|---------|----------|------|-------------|
| `GET` | `/api/v1/reservations` | Admin | Toutes les réservations |
| `GET` | `/api/v1/reservations/my` | Client | Mes réservations |
| `POST` | `/api/v1/reservations` | Client | Créer une réservation |
| `GET` | `/api/v1/reservations/{id}` | Les deux | Détail |
| `PUT` | `/api/v1/reservations/{id}` | Les deux | Modifier |
| `PATCH` | `/api/v1/reservations/{id}/cancel` | Les deux | Annuler |
| `PATCH` | `/api/v1/reservations/{id}/confirm` | Admin | Confirmer |
| `PATCH` | `/api/v1/reservations/{id}/no-show` | Admin | Marquer no-show |

#### Administration
| Méthode | Endpoint | Description |
|---------|----------|-------------|
| `GET` | `/api/v1/admin/users` | Liste des clients |
| `GET` | `/api/v1/admin/users/{id}` | Détail client + historique |
| `PATCH` | `/api/v1/admin/users/{id}/toggle` | Activer/désactiver compte |
| `GET` | `/api/v1/admin/stats` | Statistiques globales |

---

## 🔐 Authentification

L'API utilise **Laravel Sanctum** pour l'authentification par token Bearer.

```bash
# 1. Se connecter
POST /api/v1/auth/login
{
    "email": "admin@restomate.com",
    "password": "password123"
}

# 2. Utiliser le token retourné dans le header
Authorization: Bearer {votre_token}
```

Dans Swagger UI, cliquer sur **"Authorize 🔓"** et entrer le token.

---

## 👥 Rôles & Permissions

| Action | Client | Admin |
|--------|--------|-------|
| Voir restaurants/tables | ✅ | ✅ |
| Créer/modifier restaurant | ❌ | ✅ |
| Réserver une table | ✅ | ❌ |
| Voir ses réservations | ✅ | ✅ |
| Confirmer une réservation | ❌ | ✅ |
| Annuler une réservation | ✅ (la sienne) | ✅ |
| Marquer no-show | ❌ | ✅ |
| Gérer les utilisateurs | ❌ | ✅ |
| Voir les statistiques | ❌ | ✅ |

---

## 👨‍💻 Auteur

**Alex Yessougnon AGO**
Développeur Mobile & Backend — Cotonou, Bénin

[![GitHub](https://img.shields.io/badge/GitHub-Alex--AGO-181717?style=flat&logo=github)](https://github.com/Alex-Yessougnon-AGO)

---

## 📄 Licence

Ce projet est développé dans le cadre d'un concours. Tous droits réservés.