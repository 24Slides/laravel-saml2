<?php

declare(strict_types=1);

namespace Slides\Saml2\Tests;

use Slides\Saml2\Models\Tenant;
use Slides\Saml2\Helpers\TenantWrapper;
use PHPUnit\Framework\TestCase;
use Mockery;

class TenantTest extends TestCase
{
    private string $mockUuid = "mock-uuid";
    private string $stubbedUrlRouteForMetadata = "stub-route-for saml.metadata with uuid mock-uuid";

    public function setUp(): void
    {
        parent::setUp();

        // Mock laravel URL facade (would be so much easier if the library depended on laravel/framework
        // and used the laravel TestCase override that boots the laravel app! But we can still do it)
        // Do this in setUp, because tearDown's Mockery::close() will destroy it (after every test)
        $stubUrlRoute = function (string $name, $parameters = [], bool $absolute = true) {
            return "stub-route-for $name with uuid {$parameters['uuid']}";
        };

        $urlFacadeMock = \Mockery::mock('alias:Illuminate\Support\Facades\URL');
        $urlFacadeMock->shouldReceive('route')->andReturnUsing($stubUrlRoute);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    public function test_getSpEntityId_overrideNull_ShouldUseDefault()
    {
        $spEntityIdOverride = null;
        $expectedSpEntityId = $this->stubbedUrlRouteForMetadata;

        $tenant = $this->mockTenant($spEntityIdOverride);
        $this->assertEquals(
            $expectedSpEntityId,
            TenantWrapper::with($tenant)->getSpEntityId(),
            'Should return the default SP Entity ID (metadata URL) when no override is set'
        );
    }

    public function test_getSpEntityId_overrideEmptyString_ShouldUseDefault()
    {
        $spEntityIdOverride = '';
        $expectedSpEntityId = $this->stubbedUrlRouteForMetadata;

        $tenant = $this->mockTenant($spEntityIdOverride);
        $this->assertEquals(
            $expectedSpEntityId,
            TenantWrapper::with($tenant)->getSpEntityId(),
            'Should return the default SP Entity ID (metadata URL) when no override is set'
        );
    }

    public function test_getSpEntityId_overrideSet_ShouldReturnOverride()
    {
        $spEntityIdOverride = 'manually overidden sp Entity ID';
        $expectedSpEntityId = $spEntityIdOverride;

        $tenant = $this->mockTenant($spEntityIdOverride);
        $this->assertEquals(
            $expectedSpEntityId,
            TenantWrapper::with($tenant)->getSpEntityId(),
            'Should return the override value, because sp_entity_id_override is set'
        );
    }

    /**
     * Create a fake tenant.
     *
     * @return \Slides\Saml2\Models\Tenant
     */
    protected function mockTenant(?string $spEntityIdOverride = '')
    {
        $tenant = Mockery::mock(Tenant::class);
        $tenant->shouldReceive('getAttribute')->with('uuid')->andReturn($this->mockUuid);
        $tenant->shouldReceive('getAttribute')->with('sp_entity_id_override')->andReturn($spEntityIdOverride);
        return $tenant;
    }
}
