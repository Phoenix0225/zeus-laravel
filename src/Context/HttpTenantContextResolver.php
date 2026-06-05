<?php

declare(strict_types=1);

namespace Zeus\Laravel\Context;

use Illuminate\Support\Facades\DB;
use Zeus\Core\Context\TenantContext;
use Zeus\Core\Contracts\TenantContextResolverInterface;
use Zeus\Core\Security\RoleRegistry;

class HttpTenantContextResolver implements TenantContextResolverInterface
{
    public function __construct(
        private readonly RoleRegistry $roleRegistry
    ) {}

    public function resolve(): TenantContext
    {
        $request = request();

        $companyId = $this->parseHeader('X-Company-Id');
        $divisionId = $this->parseHeader('X-Division-Id');
        $siteId = $this->parseHeader('X-Site-Id');
        $warehouseId = $this->parseHeader('X-Warehouse-Id');

        $activeTenantId = $warehouseId ?? $siteId ?? $divisionId ?? $companyId;
        
        $permissions = [];
        $user = auth()->user();

        if ($user && $activeTenantId) {
            $pivot = DB::table('zeus_tenant_user')
                ->where('user_id', $user->getAuthIdentifier())
                ->where('tenant_id', $activeTenantId)
                ->first();

            if ($pivot && isset($pivot->role)) {
                $permissions = $this->roleRegistry->getPermissions($pivot->role);
            }
        }

        return new TenantContext(
            companyId: $companyId,
            divisionId: $divisionId,
            siteId: $siteId,
            warehouseId: $warehouseId,
            permissions: $permissions
        );
    }

    private function parseHeader(string $name): string|int|null
    {
        $value = request()->header($name);

        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value) && ctype_digit($value)) {
            return (int) $value;
        }

        return $value;
    }
}
