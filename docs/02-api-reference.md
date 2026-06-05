# L'API REST Dynamique

Contrairement à une application Laravel standard où le développeur doit coder un Contrôleur pour chaque Modèle Eloquent (UsersController, InvoicesController, etc.), l'adaptateur `zeus-laravel` dispose d'un **Contrôleur Unique** propulsé par la métadonnée du noyau.

L'`EntityController` est le pont universel entre les requêtes HTTP (via les routes dynamiques) et le Data Engine.

## 1. Structure Universelle

Toutes les requêtes vers les entités transitent par la nomenclature universelle :
`/api/dynamic/{entityCode}`

Dès que la requête est reçue, l'`EntityRegistry` tente d'identifier l'entité. Si elle existe (même si elle vient d'être créée en No-Code 5 secondes plus tôt), l'API y répond de manière native.

## 2. Le Cycle HTTP REST

| Verbe | Endpoint | Méthode Controller | Résultat JSON |
|-------|----------|--------------------|---------------|
| `GET` | `/api/dynamic/invoices` | `index()` | Liste des factures au format JSON (Extraction de `EntityRecord->data`) |
| `POST` | `/api/dynamic/invoices` | `store()` | Création et retour de `{"id": "..."}` avec un statut HTTP `201` |
| `PUT` | `/api/dynamic/invoices/{id}` | `update()` | Mise à jour intégrale. Retourne `{"success": true}` |
| `DELETE` | `/api/dynamic/invoices/{id}` | `destroy()` | Suppression (soft ou hard) et statut HTTP `204 No Content` |

## 3. Transformation des DTOs en JSON

Dans la méthode `index()`, les résultats sont extraits du DTO immuable du noyau. Le Controller ne retourne pas les objets complets `EntityRecord`, il aplatit la réponse pour optimiser la sérialisation HTTP pour le Frontend :

```php
// Extrait du EntityController::index
$query = $this->queryBuilder->forEntity($entity)->getQuery();
$records = $this->reader->fetch($query);

// Seul le tableau $data est encodé vers le client REST
return response()->json(array_map(fn(EntityRecord $r) => $r->data, $records));
```

Cette sérialisation stricte prévient la fuite d'informations (Metadata structurelle interne de Zeus) vers l'interface utilisateur.
