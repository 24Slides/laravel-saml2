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
    use CreatesApplication; // Standard Laravel approach to functional tests that can resolve route URLs

    private string $mockUuid = "mock-uuid";
    private string $stubbedMetadataFullUrl = "stub-app-url/stub-path-for saml.metadata with uuid mock-uuid";
    private string $stubbedMetadataPath = "stub-path-for saml.metadata with uuid mock-uuid";

    public function setUp(): void
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
        $expectedSpEntityId = $this->stubbedMetadataFullUrl;

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
        $expectedSpEntityId = $this->stubbedMetadataFullUrl;

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
        $expectedSpEntityId = 'https://manually-overidden-domain/' . $this->stubbedMetadataPath;

        $tenant = $this->mockTenant($idAppUrlOverride);
        $this->assertEquals(
            $expectedSpEntityId,
            TenantWrapper::with($tenant)->getSpEntityId(),
            'Should return the override value, because id_app_url_override is set'
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
}
