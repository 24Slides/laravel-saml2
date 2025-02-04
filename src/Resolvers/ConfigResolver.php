<?php

namespace Slides\Saml2\Resolvers;

use Slides\Saml2\Contracts\IdentityProvidable;
use Slides\Saml2\Contracts\ResolvesIdpConfig;
use Slides\Saml2\Exceptions\ConfigurationException;

class ConfigResolver implements ResolvesIdpConfig
{
    /**
     * Adjust SAML configuration for the given identity provider.
     *
     * @param IdentityProvidable $idp
     * @param array $config
     *
     * @return array
     */
    public function resolve(IdentityProvidable $idp, array $config): array
    {
        if ($idp->idpX509cert() === null) {
            throw new ConfigurationException('Identity Provider certificate is missing');
        }

        $config['idp'] = [
            'entityId' => $idp->idpEntityId(),
            'singleSignOnService' => ['url' => $idp->idpLoginUrl()],
            'singleLogoutService' => ['url' => $idp->idpLogoutUrl()],
            'x509cert' => $idp->idpX509cert()
        ];

        $config['sp']['NameIDFormat'] = $this->resolveNameIdFormatPrefix($idp->idpNameIdFormat());

        return $config;
    }

    /**
     * Resolve the Name ID Format prefix.
     *
     * @param string $format
     *
     * @return string
     */
    protected function resolveNameIdFormatPrefix(string $format): string
    {
        switch ($format) {
            case 'emailAddress':
            case 'X509SubjectName':
            case 'WindowsDomainQualifiedName':
            case 'unspecified':
                return 'urn:oasis:names:tc:SAML:1.1:nameid-format:' . $format;
            default:
                return 'urn:oasis:names:tc:SAML:2.0:nameid-format:'. $format;
        }
    }
}
