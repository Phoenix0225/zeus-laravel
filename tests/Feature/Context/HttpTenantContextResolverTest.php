<?php

declare(strict_types=1);

namespace Zeus\Laravel\Tests\Feature\Context;

use Illuminate\Http\Request;
use Zeus\Laravel\Context\HttpTenantContextResolver;
use Zeus\Laravel\Tests\TestCase;

class HttpTenantContextResolverTest extends TestCase
{
    public function test_it_resolves_a_global_context_when_no_headers_are_provided(): void
    {
        $request = Request::create('/api/test', 'GET');
        $resolver = new HttpTenantContextResolver($request);

        $context = $resolver->resolve();

        $this->assertTrue($context->isGlobal());
    }

    public function test_it_resolves_specific_context_from_http_headers(): void
    {
        $request = Request::create('/api/test', 'GET');
        $request->headers->set('X-Company-Id', '1');
        $request->headers->set('X-Site-Id', 'MTL-01');

        $resolver = new HttpTenantContextResolver($request);
        $context = $resolver->resolve();

        $this->assertSame(1, $context->companyId);
        $this->assertSame('MTL-01', $context->siteId);
        $this->assertSame('site', $context->getLevel());
    }
}
