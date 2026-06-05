# Résolution du Contexte via HTTP (Multi-Tenant)

Afin d'opérer avec `zeus-core`, l'adaptateur Laravel doit obligatoirement implémenter le contrat `TenantContextResolverInterface` pour fournir au noyau l'identité, le scope (périmètre) et les permissions du requérant.

Dans l'environnement Laravel, ce contrat est brillamment résolu par le `HttpTenantContextResolver`.

## 1. Injection des Headers HTTP

Le paradigme SaaS sans état (Stateless) suppose que chaque requête API soit autosuffisante. Le frontend ou le client distant doit indiquer à l'ERP sous quel "Chapeau" (Contexte) il désire opérer, en transmettant des Headers HTTP spécifiques :

- `X-Company-Id`
- `X-Division-Id`
- `X-Site-Id`
- `X-Warehouse-Id`

Si un utilisateur possède des accès sur l'usine (Site) `MTL-01` et qu'il transmet le Header `X-Site-Id: 5`, la hiérarchie matricielle de Zeus considèrera le "Site 5" comme le niveau actif de la transaction.

## 2. Interfaçage avec la Sécurité (ACL Data-Driven)

Le `HttpTenantContextResolver` ne se contente pas de traduire les Headers. Il est le garant de la Sécurité ACL à la frontière de l'application.

Dans la méthode `resolve()`, le résolveur :
1. Extrait l'utilisateur authentifié (via `auth()->user()`).
2. Identifie le niveau Tenant le plus "profond" actif de la requête.
3. Exécute une requête (via la façade DB native) sur la table pivot `zeus_tenant_user` pour y trouver l'affectation physique de l'utilisateur.
4. Si un rôle est trouvé (ex: `reader`), le résolveur interroge le `RoleRegistry` en mémoire de `zeus-core`.

```php
// Résolution sécurisée du Contexte et des ACL
if ($user && $activeTenantId) {
    $pivot = DB::table('zeus_tenant_user')
        ->where('user_id', $user->getAuthIdentifier())
        ->where('tenant_id', $activeTenantId)
        ->first();

    if ($pivot && isset($pivot->role)) {
        $permissions = $this->roleRegistry->getPermissions($pivot->role); // Ex: ['*.read']
    }
}
```

Enfin, ces permissions récupérées sont greffées de manière définitive et en lecture seule (`readonly`) dans le `TenantContext` poussé vers le noyau. Si le contexte s'avère dépourvu des permissions suffisantes lors d'une écriture ou d'une lecture, le `SecurityEnforcer` bloquera immédiatement la transaction avec un statut 500 ou 403.
