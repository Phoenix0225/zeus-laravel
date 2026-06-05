# ⚡ Zeus Laravel
![License](https://img.shields.io/badge/License-BSL%201.1-green)
![Laravel Version](https://img.shields.io/badge/Laravel-13.x-red.svg)
![PHP Version](https://img.shields.io/badge/PHP-8.4+-blue.svg)

Zeus Laravel est l'adaptateur officiel pour connecter `zeus-core` à Laravel. Il fournit une intégration transparente, gérant le SQL, exposant une API dynamique, et proposant une installation "Clé en main" pour vos applications Data-Driven.

## Fonctionnalités Principales

- **API REST Dynamique** : Expose automatiquement des points de terminaison REST pour toutes vos entités configurées.
- **Traducteur SQL** : Convertit l'Abstract Syntax Tree (AST) des requêtes agnostiques de Zeus Core en requêtes optimisées `Illuminate\Database\Query\Builder`.
- **Résolution Multi-Tenant via Headers HTTP** : Gère automatiquement le contexte tenant à partir des requêtes HTTP pour une sécurité transparente.

## Installation & Déploiement

```bash
composer require ton-nom/zeus-laravel
```

Installation complète "Clé en main" :

```bash
php artisan zeus:install
```
*Note : Cette commande configure la base de données, crée le tenant "HQ" par défaut, et génère l'utilisateur "admin@zeus.local".*

## 📖 Documentation

- [1. Guide de Démarrage & Installation](docs/01-getting-started.md)
- [2. Référence de l'API Dynamique REST](docs/02-api-reference.md)
- [3. Contexte HTTP, Headers & Sécurité (ACL)](docs/03-http-context.md)

## Endpoints Dynamiques

L'adaptateur génère dynamiquement les routes suivantes pour chaque entité (ex: `invoice`) :

| Méthode | Endpoint | Description |
|---|---|---|
| `GET` | `/api/dynamic/{entityCode}` | Liste les enregistrements (avec support de pagination et filtrage) |
| `POST` | `/api/dynamic/{entityCode}` | Crée un nouvel enregistrement |
| `GET` | `/api/dynamic/{entityCode}/{id}` | Récupère un enregistrement spécifique |
| `PUT` | `/api/dynamic/{entityCode}/{id}` | Met à jour un enregistrement existant |
| `DELETE`| `/api/dynamic/{entityCode}/{id}` | Supprime un enregistrement |

Ainsi que les routes pour le registre UI :

| Méthode | Endpoint | Description |
|---|---|---|
| `GET` | `/api/ui/menus` | Récupère la structure des menus de l'application |
| `GET` | `/api/ui/screens/{screenCode}` | Récupère la configuration d'un écran spécifique |