<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Use built-in routes
    |--------------------------------------------------------------------------
    |
    | If "useRoutes" set to true, the package defines five new routes:
    |
    | Method | URI                      | Name
    | -------|--------------------------|------------------
    | POST   | {routesPrefix}/acs       | saml_acs
    | GET    | {routesPrefix}/login     | saml_login
    | GET    | {routesPrefix}/logout    | saml_logout
    | GET    | {routesPrefix}/metadata  | saml_metadata
    | GET    | {routesPrefix}/sls       | saml_sls
    |
    */

    'useRoutes' => true,

    /*
    |--------------------------------------------------------------------------
    | Built-in routes prefix
    |--------------------------------------------------------------------------
    |
    | Here you may define the prefix for built-in routes.
    |
    */

    'routesPrefix' => '/saml',

    /*
    |--------------------------------------------------------------------------
    | Middle groups to use for the SAML routes
    |--------------------------------------------------------------------------
    |
    | Note, Laravel 5.2 requires a group which includes StartSession
    |
    */

    'routesMiddleware' => [],

    /*
    |--------------------------------------------------------------------------
    | Signature validation
    |--------------------------------------------------------------------------
    |
    | Set to true if you want to use parameters from $_SERVER to validate the signature.
    |
    */

    'retrieveParametersFromServer' => false,

    /*
    |--------------------------------------------------------------------------
    | Login redirection URL.
    |--------------------------------------------------------------------------
    |
    | The redirection URL after successful login.
    |
    */

    'loginRoute' => env('SAML2_LOGIN_URL'),

    /*
    |--------------------------------------------------------------------------
    | Logout redirection URL.
    |--------------------------------------------------------------------------
    |
    | The redirection URL after successful logout.
    |
    */

    'logoutRoute' => env('SAML2_LOGOUT_URL'),


    /*
    |--------------------------------------------------------------------------
    | Login error redirection URL.
    |--------------------------------------------------------------------------
    |
    | The redirection URL after login failing.
    |
    */

    'errorRoute' => env('SAML2_ERROR_URL'),

    /*
    |--------------------------------------------------------------------------
    | Strict mode.
    |--------------------------------------------------------------------------
    |
    | If 'strict' is True, then the PHP Toolkit will reject unsigned
    | or unencrypted messages if it expects them signed or encrypted
    | Also will reject the messages if not strictly follow the SAML
    | standard: Destination, NameId, Conditions... are validated too.
    |
    */

    'strict' => true,

    /*
    |--------------------------------------------------------------------------
    | Debug mode.
    |--------------------------------------------------------------------------
    |
    | When enabled, errors must be printed.
    |
    */

    'debug' => env('SAML2_DEBUG', env('APP_DEBUG', false)),

    /*
    |--------------------------------------------------------------------------
    | Whether to use `X-Forwarded-*` headers to determine port/domain/protocol.
    |--------------------------------------------------------------------------
    |
    | If 'proxyVars' is True, then the Saml lib will trust proxy headers
    | e.g X-Forwarded-Proto / HTTP_X_FORWARDED_PROTO. This is useful if
    | your application is running behind a load balancer which terminates SSL.
    |
    */

    'proxyVars' => false,

    /*
    |--------------------------------------------------------------------------
    | Service Provider configuration.
    |--------------------------------------------------------------------------
    |
    | General setting of the service provider.
    |
    */

    'sp' => [

        // Specifies constraints on the name identifier to be used to
        // represent the requested subject.
        // Take a look on lib/Saml2/Constants.php to see the NameIdFormat supported
        'NameIDFormat' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',

        // Usually x509cert and privateKey of the SP are provided by files placed at
        // the certs folder. But we can also provide them with the following parameters
        'x509cert' => env('SAML2_SP_x509',''),
        'privateKey' => env('SAML2_SP_PRIVATEKEY',''),

        // Identifier (URI) of the SP entity.
        // Leave blank to use the 'saml_metadata' route.
        'entityId' => env('SAML2_SP_ENTITYID',''),

        // Specifies info about where and how the <AuthnResponse> message MUST be
        // returned to the requester, in this case our SP.
        'assertionConsumerService' => [
            // URL Location where the <Response> from the IdP will be returned,
            // using HTTP-POST binding.
            // Leave blank to use the 'saml_acs' route
            'url' => '',
        ],
        // Specifies info about where and how the <Logout Response> message MUST be
        // returned to the requester, in this case our SP.
        // Remove this part to not include any URL Location in the metadata.
        'singleLogoutService' => [
            // URL Location where the <Response> from the IdP will be returned,
            // using HTTP-Redirect binding.
            // Leave blank to use the 'saml_sls' route
            'url' => '',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Identity Providers.
    |--------------------------------------------------------------------------
    |
    | Here you man define multiple identity providers you're going to connect with.
    |
    */

    'idp' => [
        'default' => env('SAML2_IDP_DEFAULT', 'oneLogin'),

        'oneLogin' => [
            'issuerURL' => env('SAML2_IDP_ONE_LOGIN_ISSUER_URL'),

            'singleSignOnService' => [
                'url' => env('SAML2_IDP_ONE_LOGIN_SERVICE_URL'),
            ],

            'singleLogoutService' => [
                'url' => env('SAML2_IDP_ONE_LOGIN_LOGOUT_URL'),
            ],

            // Public x509 certificate of the IdP
            'x509cert' => env('SAML2_IDP_ONE_LOGIN_CERT_x509'),

            /*
             *  Instead of use the whole x509cert you can use a fingerprint
             *  (openssl x509 -noout -fingerprint -in "idp.crt" to generate it)
             */
            // 'certFingerprint' => '',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | OneLogin security settings.
    |--------------------------------------------------------------------------
    |
    |
    |
    */

    'security' => [

        /*
        |--------------------------------------------------------------------------
        | NameId encryption
        |--------------------------------------------------------------------------
        |
        | Indicates that the nameID of the <samlp:logoutRequest> sent by this SP
        | will be encrypted.
        |
        */

        'nameIdEncrypted' => false,

        /*
        |--------------------------------------------------------------------------
        | AuthnRequest signage
        |--------------------------------------------------------------------------
        |
        | Indicates whether the <samlp:AuthnRequest> messages sent by
        | this SP will be signed. The Metadata of the SP will offer this info
        |
        */

        'authnRequestsSigned' => false,

        /*
        |--------------------------------------------------------------------------
        | Logout request signage
        |--------------------------------------------------------------------------
        |
        | Indicates whether the <samlp:logoutRequest> messages sent by this SP
        | will be signed.
        |
        */

        'logoutRequestSigned' => false,

        /*
        |--------------------------------------------------------------------------
        | Logout response signage
        |--------------------------------------------------------------------------
        |
        | Indicates whether the <samlp:logoutResponse> messages sent by this SP
        | will be signed.
        |
        */

        'logoutResponseSigned' => false,

        /*
        |--------------------------------------------------------------------------
        | Whether need to sign metadata.
        |--------------------------------------------------------------------------
        |
        | The possible values:
        | - false
        | - true (use certs)
        | - array:
        |   ```
        |   [
        |       'keyFileName' => 'metadata.key',
        |       'certFileName' => 'metadata.crt'
        |   ]
        |   ```
        |
        */

        'signMetadata' => false,

        /*
        |--------------------------------------------------------------------------
        | Requirement to sign messages.
        |--------------------------------------------------------------------------
        |
        | Indicates a requirement for the <samlp:Response>, <samlp:LogoutRequest> and
        | <samlp:LogoutResponse> elements received by this SP to be signed.
        |
        */

        'wantMessagesSigned' => false,

        /*
        |--------------------------------------------------------------------------
        | Requirement to sign assertion elements.
        |--------------------------------------------------------------------------
        |
        | Indicates a requirement for the <saml:Assertion> elements received by
        | this SP to be signed.
        |
        */

        'wantAssertionsSigned' => false,

        /*
        |--------------------------------------------------------------------------
        | Requirement to encrypt NameID.
        |--------------------------------------------------------------------------
        |
        | Indicates a requirement for the NameID received by this SP to be encrypted.
        |
        */

        'wantNameIdEncrypted' => false,

        /*
        |--------------------------------------------------------------------------
        | Authentication context.
        |--------------------------------------------------------------------------
        |
        | Set to false and no AuthContext will be sent in the AuthNRequest,
        |
        | Set true or don't present this parameter and you will get an
        | AuthContext 'exact' 'urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport'
        |
        | Set an array with the possible auth context values:
        | ['urn:oasis:names:tc:SAML:2.0:ac:classes:Password', 'urn:oasis:names:tc:SAML:2.0:ac:classes:X509']
        |
        */

        'requestedAuthnContext' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Contact information.
    |--------------------------------------------------------------------------
    |
    | It is recommended to supply a technical and support contacts.
    |
    */

    'contactPerson' => [
        'technical' => [
            'givenName' => 'name',
            'emailAddress' => 'no@reply.com'
        ],
        'support' => [
            'givenName' => 'Support',
            'emailAddress' => 'no@reply.com'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Organization information.
    |--------------------------------------------------------------------------
    |
    | The info in en_US lang is recommended, add more if required.
    |
    */

    'organization' => [
        'en-US' => [
            'name' => 'Name',
            'displayname' => 'Display Name',
            'url' => 'http://url'
        ],
    ],

];
