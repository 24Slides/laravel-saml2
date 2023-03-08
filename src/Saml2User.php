<?php

namespace Slides\Saml2;

use OneLogin\Saml2\Auth as OneLoginAuth;
use Slides\Saml2\Contracts\IdentityProvider;

class Saml2User
{
    /**
     * OneLogin authentication handler.
     *
     * @var OneLoginAuth
     */
    protected $auth;

    /**
     * The tenant user belongs to.
     *
     * @var IdentityProvider
     */
    protected $idp;

    /**
     * Saml2User constructor.
     *
     * @param OneLoginAuth $auth
     * @param IdentityProvider $idp
     */
    public function __construct(OneLoginAuth $auth, IdentityProvider $idp)
    {
        $this->auth = $auth;
        $this->idp = $idp;
    }

    /**
     * Get the user ID retrieved from assertion processed this request.
     *
     * @return string
     */
    public function getUserId()
    {
        return $this->auth->getNameId();
    }

    /**
     * Get the attributes retrieved from assertion processed this request
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->auth->getAttributes();
    }

    /**
     * Returns the requested SAML attribute
     *
     * @param string $name The requested attribute of the user.
     *
     * @return array|null Requested SAML attribute ($name).
     */
    public function getAttribute($name)
    {
        return $this->auth->getAttribute($name);
    }

    /**
     * The attributes retrieved from assertion processed this request.
     *
     * @return array
     */
    public function getAttributesWithFriendlyName()
    {
        return $this->auth->getAttributesWithFriendlyName();
    }

    /**
     * The SAML assertion processed this request.
     *
     * @return string
     */
    public function getRawSamlAssertion()
    {
        return app('request')->input('SAMLResponse'); //just this request
    }

    /**
     * Get the intended URL.
     *
     * @return mixed
     */
    public function getIntendedUrl()
    {
        $relayState = app('request')->input('RelayState');

        $url = app('Illuminate\Contracts\Routing\UrlGenerator');

        if ($relayState && $url->full() != $relayState) {
            return $relayState;
        }

        return null;
    }

    /**
     * Parses a SAML property and adds this property to this user or returns the value.
     *
     * @param string $samlAttribute
     * @param string $propertyName
     *
     * @return array|null
     */
    public function parseUserAttribute($samlAttribute = null, $propertyName = null)
    {
        if (empty($samlAttribute)) {
            return null;
        }

        if (empty($propertyName)) {
            return $this->getAttribute($samlAttribute);
        }

        return $this->{$propertyName} = $this->getAttribute($samlAttribute);
    }

    /**
     * Parse the SAML attributes and add them to this user.
     *
     * @param array $attributes Array of properties which need to be parsed, like ['email' => 'urn:oid:0.9.2342.19200300.100.1.3']
     *
     * @return void
     */
    public function parseAttributes($attributes = [])
    {
        foreach ($attributes as $propertyName => $samlAttribute) {
            $this->parseUserAttribute($samlAttribute, $propertyName);
        }
    }

    /**
     * Get user's session index.
     *
     * @return null|string
     */
    public function getSessionIndex(): ?string
    {
        return $this->auth->getSessionIndex();
    }

    /**
     * Get user's name ID.
     *
     * @return string
     */
    public function getNameId(): string
    {
        return $this->auth->getNameId();
    }

    /**
     * Set a tenant
     *
     * @param IdentityProvider $idp
     *
     * @return void
     */
    public function setIdp(IdentityProvider $idp)
    {
        $this->idp = $idp;
    }

    /**
     * Get a resolved tenant.
     *
     * @return IdentityProvider|null
     */
    public function getIdp()
    {
        return $this->idp;
    }
}
