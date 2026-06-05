# Démarrage et "Clé en main"

L'adaptateur **Zeus Laravel** a pour vocation d'exposer la complexité et l'abstraction du noyau (`zeus-core`) via une interface familière, performante et prête à l'emploi. Sa philosophie est le "Plug & Play" absolu pour des équipes habituées à l'écosystème Laravel.

## 1. Installation

Une fois le package installé via Composer, il n'est pas nécessaire de configurer manuellement les migrations ou de jongler avec les structures de rôles de départ. L'adaptateur fournit un installateur global.

```bash
php artisan zeus:install
```

Cette commande unifiée réalise trois opérations critiques :
1. **Validation** : Requiert l'autorisation de l'utilisateur pour procéder.
2. **Migration** : Lance les migrations système (création de `zeus_tenants` et de la table pivot de sécurité `zeus_tenant_user`).
3. **Seeding** : Fait appel au `ZeusDatabaseSeeder` pour injecter les données de démarrage vitales.

## 2. L'Expérience "Clé en Main"

Le seeder d'installation s'assure qu'un environnement fonctionnel complet est instancié dès la fin de la commande. 

Il crée spécifiquement :
- **Un compte administrateur** : `admin@zeus.local` (Mot de passe: `password`)
- **Le premier Tenant (Locataire)** : Rattaché au code `HQ` (Quartier Général).
- **L'assignation des permissions** : Lie le compte administrateur au Quartier Général avec le rôle suprême `admin` (lequel se résout via l'ACL en `['*']`).

Dès lors, l'API est intégralement prête à recevoir des requêtes authentifiées sans nécessiter de configuration supplémentaire.
