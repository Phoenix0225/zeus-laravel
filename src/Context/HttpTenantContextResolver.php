<?php

declare(strict_types=1);

namespace Zeus\Laravel\Context;

use Illuminate\Http\Request;
use Zeus\Core\Context\TenantContext;
use Zeus\Core\Contracts\TenantContextResolverInterface;

class HttpTenantContextResolver implements TenantContextResolverInterface
{
    public function __construct(private readonly Request $request)
    {
    }

    public function resolve(): TenantContext
    {
        return new TenantContext(
            companyId: $this->parseHeader('X-Company-Id'),
            divisionId: $this->parseHeader('X-Division-Id'),
            siteId: $this->parseHeader('X-Site-Id'),
            warehouseId: $this->parseHeader('X-Warehouse-Id'),
        );
    }

    private function parseHeader(string $name): string|int|null
    {
        $value = $this->request->header($name);

        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value) && ctype_digit($value)) {
            return (int) $value;
        }

        return $value;
    }
}
