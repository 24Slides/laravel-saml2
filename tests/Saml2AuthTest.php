<?php

namespace Slides\Saml2\Tests;

use PHPUnit\Framework\TestCase;
use Slides\Saml2\Auth;

class Saml2AuthTest extends TestCase
{
    public function tearDown(): void
    {
        \Mockery::close();
    }

    public function testIsAuthenticated()
    {
        $oneLoginAuth = \Mockery::mock(\OneLogin\Saml2\Auth::class);
        $saml2Auth = new Auth($oneLoginAuth, $this->mockIdp());

        $oneLoginAuth->shouldReceive('isAuthenticated')->andReturn(true);

        $this->assertEquals(true, $saml2Auth->isAuthenticated());
    }

    public function testLogin()
    {
        $oneLoginAuth = \Mockery::mock(\OneLogin\Saml2\Auth::class);
        $saml2Auth = new Auth($oneLoginAuth, $this->mockIdp());

        $oneLoginAuth->shouldReceive('login')->once();

        $saml2Auth->login();

        $this->addToAssertionCount(1);
    }

    public function testLogout()
    {
        $expectedReturnTo = 'http://localhost';
        $expectedSessionIndex = 'session_index_value';
        $expectedNameId = 'name_id_value';
        $expectedNameIdFormat = 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified';
        $expectedStay = true;
        $expectedNameIdNameQualifier = 'name_id_name_qualifier';

        $oneLoginAuth = \Mockery::mock(\OneLogin\Saml2\Auth::class);
        $saml2Auth = new Auth($oneLoginAuth, $this->mockIdp());

        $oneLoginAuth->shouldReceive('logout')
            ->with($expectedReturnTo, [], $expectedNameId, $expectedSessionIndex, $expectedStay, $expectedNameIdFormat, $expectedNameIdNameQualifier)
            ->once();

        $saml2Auth->logout($expectedReturnTo, $expectedNameId, $expectedSessionIndex, $expectedNameIdFormat, $expectedStay, $expectedNameIdNameQualifier);

        $this->addToAssertionCount(1);
    }

    public function testAcsError()
    {
        $oneLoginAuth = \Mockery::mock(\OneLogin\Saml2\Auth::class);
        $saml2Auth = new Auth($oneLoginAuth, $this->mockIdp());
        $oneLoginAuth->shouldReceive('processResponse')->once();
        $oneLoginAuth->shouldReceive('getErrors')->once()->andReturn(array('errors'));

        $error = $saml2Auth->acs();

        $this->assertNotEmpty($error);
    }


    public function testAcsNotAutenticated()
    {
        $oneLoginAuth = \Mockery::mock(\OneLogin\Saml2\Auth::class);
        $saml2Auth = new Auth($oneLoginAuth, $this->mockIdp());
        $oneLoginAuth->shouldReceive('processResponse')->once();
        $oneLoginAuth->shouldReceive('getErrors')->once()->andReturn(null);
        $oneLoginAuth->shouldReceive('isAuthenticated')->once()->andReturn(false);
        $error =  $saml2Auth->acs();

        $this->assertNotEmpty($error);
    }


    public function testAcsOK()
    {
        $oneLoginAuth = \Mockery::mock(\OneLogin\Saml2\Auth::class);
        $saml2Auth = new Auth($oneLoginAuth, $this->mockIdp());
        $oneLoginAuth->shouldReceive('processResponse')->once();
        $oneLoginAuth->shouldReceive('getErrors')->once()->andReturn(null);
        $oneLoginAuth->shouldReceive('isAuthenticated')->once()->andReturn(true);

        $error =  $saml2Auth->acs();

        $this->assertEmpty($error);
    }

    public function testSlsError()
    {
        $oneLoginAuth = \Mockery::mock(\OneLogin\Saml2\Auth::class);
        $saml2Auth = new Auth($oneLoginAuth, $this->mockIdp());
        $oneLoginAuth->shouldReceive('processSLO')->once();
        $oneLoginAuth->shouldReceive('getErrors')->once()->andReturn('errors');

        $error =  $saml2Auth->sls();

        $this->assertNotEmpty($error);
    }

    public function testSlsOK()
    {
        $oneLoginAuth = \Mockery::mock(\OneLogin\Saml2\Auth::class);
        $saml2Auth = new Auth($oneLoginAuth, $this->mockIdp());
        $oneLoginAuth->shouldReceive('processSLO')->once();
        $oneLoginAuth->shouldReceive('getErrors')->once()->andReturn(null);

        $error =  $saml2Auth->sls();

        $this->assertEmpty($error);
    }

    public function testCanGetLastError()
    {
        $oneLoginAuth = \Mockery::mock(\OneLogin\Saml2\Auth::class);
        $saml2Auth = new Auth($oneLoginAuth, $this->mockIdp());

        $oneLoginAuth->shouldReceive('getLastErrorReason')->andReturn('lastError');

        $this->assertSame('lastError', $saml2Auth->getLastErrorReason());
    }

    public function testGetUserAttribute() {
        $oneLoginAuth = \Mockery::mock(\OneLogin\Saml2\Auth::class);
        $saml2Auth = new Auth($oneLoginAuth, $this->mockIdp());

        $user = $saml2Auth->getSaml2User();

        $oneLoginAuth->shouldReceive('getAttribute')
            ->with('urn:oid:0.9.2342.19200300.100.1.3')
            ->andReturn(['test@example.com']);

        $this->assertEquals(['test@example.com'], $user->getAttribute('urn:oid:0.9.2342.19200300.100.1.3'));
    }

    public function testParseSingleUserAttribute() {
        $oneLoginAuth = \Mockery::mock(\OneLogin\Saml2\Auth::class);
        $saml2Auth = new Auth($oneLoginAuth, $this->mockIdp());

        $user = $saml2Auth->getSaml2User();

        $oneLoginAuth->shouldReceive('getAttribute')
            ->with('urn:oid:0.9.2342.19200300.100.1.3')
            ->andReturn(['test@example.com']);

        $user->parseUserAttribute('urn:oid:0.9.2342.19200300.100.1.3', 'email');

        $this->assertEquals($user->email, ['test@example.com']);
    }

    public function testParseMultipleUserAttributes() {
        $oneLoginAuth = \Mockery::mock(\OneLogin\Saml2\Auth::class);
        $saml2Auth = new Auth($oneLoginAuth, $this->mockIdp());

        $user = $saml2Auth->getSaml2User();

        $oneLoginAuth->shouldReceive('getAttribute')
            ->twice()
            ->andReturn(['test@example.com'], ['Test User']);

        $user->parseAttributes([
            'email' => 'urn:oid:0.9.2342.19200300.100.1.3',
            'displayName' => 'urn:oid:2.16.840.1.113730.3.1.241'
        ]);

        $this->assertEquals($user->email, ['test@example.com']);
        $this->assertEquals($user->displayName, ['Test User']);
    }

    /**
     * Mock the authentication handler.
     *
     * @return Auth|\Mockery\MockInterface
     */
    protected function mockAuth()
    {
        $auth = \Mockery::mock(\OneLogin\Saml2\Auth::class);

        return new Auth($auth, $this->mockIdp());
    }

    /**
     * Create a fake IdP configuration.
     *
     * @return array
     */
    protected function mockIdp()
    {
        return [
            'key' => 'idp1',
            'entityId' => 'idp.enterprise.com',
            'singleSignOnService' => [
                'url' => '',
            ],
            'singleLogoutService' => [
                'url' => '',
            ],
        ];
    }
}
