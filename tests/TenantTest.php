<?php

declare(strict_types=1);

namespace Slides\Saml2\Tests;

use Slides\Saml2\Models\Tenant;
use Slides\Saml2\Helpers\TenantWrapper;
use Illuminate\Foundation\Testing\TestCase;
use Mockery;

use Tests\CreatesApplication; // Test app context must provide this

class TenantTest extends TestCase
{
    // CreatesApplication is the standard Laravel approach to functional tests that can resolve route URLs
    use CreatesApplication;

    private string $mockUuid = 'mock-uuid';

    protected function setUp(): void
    {
        parent::setUp();

        // Mock laravel URL facade (would be so much easier if the library depended on laravel/framework
        // and used the laravel TestCase override that boots the laravel app! But we can still do it)
        // Do this in setUp, because tearDown's Mockery::close() will destroy it (after every test)
        $stubUrlRoute = function (string $name, $parameters = [], bool $absolute = true) {
            if ($absolute) {
                return "stub-app-url/stub-path-for $name with uuid {$parameters['uuid']}";
            }
            return "/stub-path-for $name with uuid {$parameters['uuid']}";
        };

        $urlFacadeMock = \Mockery::mock('alias:Illuminate\Support\Facades\URL');
        $urlFacadeMock->shouldReceive('route')->andReturnUsing($stubUrlRoute);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_getSpEntityId_appUrlOverrideNull_ShouldUseDefault()
    {
        $idAppUrlOverride = null;
        $expectedSpEntityId = $this->getStubbedRoute_FullUrl('saml.metadata');

        $tenant = $this->mockTenant($idAppUrlOverride);

        $this->assertEquals(
            $expectedSpEntityId,
            TenantWrapper::with($tenant)->getSpEntityId(),
            'Should return the default SP Entity ID (metadata URL) when no override is set'
        );
    }

    public function test_getSpEntityId_appUrlOverrideEmptyString_ShouldUseDefault()
    {
        $idAppUrlOverride = '';
        $expectedSpEntityId = $this->getStubbedRoute_FullUrl('saml.metadata');

        $tenant = $this->mockTenant($idAppUrlOverride);
        $this->assertEquals(
            $expectedSpEntityId,
            TenantWrapper::with($tenant)->getSpEntityId(),
            'Should return the default SP Entity ID (metadata URL) when no override is set'
        );
    }

    public function test_getSpEntityId_appUrlOverrideSet_ShouldReturnOverride()
    {
        $idAppUrlOverride = 'https://manually-overidden-domain';
        $expectedSpEntityId = 'https://manually-overidden-domain' . $this->getStubbedRoute_PathOnly('saml.metadata');

        $tenant = $this->mockTenant($idAppUrlOverride);
        $this->assertEquals(
            $expectedSpEntityId,
            TenantWrapper::with($tenant)->getSpEntityId(),
            'Should return the SP Entity ID path appended to the overriden value, because id_app_url_override is set'
        );
    }

    public function test_getAcsUrl_appUrlOverrideNull_ShouldUseDefault()
    {
        $idAppUrlOverride = null;
        $expectedAcsUrl = $this->getStubbedRoute_FullUrl('saml.acs');

        $tenant = $this->mockTenant($idAppUrlOverride);

        $this->assertEquals(
            $expectedAcsUrl,
            TenantWrapper::with($tenant)->getAcsUrl(),
            'Should return the default ACS URL when no override is set'
        );
    }

    public function test_getAcsUrl_appUrlOverrideEmptyString_ShouldUseDefault()
    {
        $idAppUrlOverride = '';
        $expectedAcsUrl = $this->getStubbedRoute_FullUrl('saml.acs');

        $tenant = $this->mockTenant($idAppUrlOverride);
        $this->assertEquals(
            $expectedAcsUrl,
            TenantWrapper::with($tenant)->getAcsUrl(),
            'Should return the default ACS URL when no override is set'
        );
    }

    public function test_getAcsUrl_appUrlOverrideSet_ShouldReturnOverride()
    {
        $idAppUrlOverride = 'https://manually-overidden-domain';
        $expectedAcsUrl = 'https://manually-overidden-domain' . $this->getStubbedRoute_PathOnly('saml.acs');

        $tenant = $this->mockTenant($idAppUrlOverride);
        $this->assertEquals(
            $expectedAcsUrl,
            TenantWrapper::with($tenant)->getAcsUrl(),
            'Should return the ACS path for the tenant appended to the overriden value, because id_app_url_override is set'
        );
    }

    public function test_getSlsUrl_appUrlOverrideSet_ShouldReturnOverride()
    {
        $idAppUrlOverride = 'https://manually-overidden-domain';
        $expectedSlsUrl = 'https://manually-overidden-domain' . $this->getStubbedRoute_PathOnly('saml.sls');

        $tenant = $this->mockTenant($idAppUrlOverride);
        $this->assertEquals(
            $expectedSlsUrl,
            TenantWrapper::with($tenant)->getSlsUrl(),
            'Should return the ACS path for the tenant appended to the overriden value, because id_app_url_override is set'
        );
    }

    public function test_getAcsUrl_appUrlOverrideHasTrailingSlash_shouldNotHaveDoubleSlashAfterOverwriddenBase()
    {
        $idAppUrlOverride = 'https://manually-overidden-domain/';
        $expectedAcsUrl = 'https://manually-overidden-domain' . $this->getStubbedRoute_PathOnly('saml.acs');

        $tenant = $this->mockTenant($idAppUrlOverride);
        $this->assertEquals(
            $expectedAcsUrl,
            TenantWrapper::with($tenant)->getAcsUrl(),
            'app url override should not care if it was set with trailing slash'
        );
    }

    /**
     * Create a fake tenant.
     *
     * @return \Slides\Saml2\Models\Tenant
     */
    protected function mockTenant(?string $idAppUrlOverride = '')
    {
        $tenant = Mockery::mock(Tenant::class);
        $tenant->shouldReceive('getAttribute')->with('uuid')->andReturn($this->mockUuid);
        $tenant->shouldReceive('getAttribute')->with('id_app_url_override')->andReturn($idAppUrlOverride);
        return $tenant;
    }

    protected function getStubbedRoute_FullUrl(string $route)
    {
        return "stub-app-url/stub-path-for $route with uuid mock-uuid";
    }

    protected function getStubbedRoute_PathOnly(string $route)
    {
        return "/stub-path-for $route with uuid mock-uuid";
    }
}
