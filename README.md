# ⚡ Zeus Laravel

**L'adaptateur Laravel 13 pour le moteur ERP *metadata-driven* Zeus Core.**

![PHP Version](https://img.shields.io/badge/PHP-8.4%2B-blue)
![Laravel Version](https://img.shields.io/badge/Laravel-13.x-red)
![License](https://img.shields.io/badge/License-BSL%201.1-green)

---

## 🎯 Aperçu

`zeus-laravel` est le package d'intégration officiel qui relie le moteur agnostique `zeus-core` à l'écosystème Laravel.
Il n'inclut **aucune règle d'affaires**. Sa mission stricte est de fournir au noyau abstrait les implémentations concrètes nécessaires à son fonctionnement :
- Lecture des métadonnées via la base de données MSSQL (Façade DB).
- Injection des dépendances via le Service Container de Laravel.
- Relais des événements du Core vers l'Event Bus de Laravel.
- Gestion du cache des métadonnées.

## 📦 Installation

Ce package est destiné à être utilisé comme dépendance locale ou privée. Dans l'application ERP finale, installez-le via Composer :

```bash
composer require synapse/zeus-laravel