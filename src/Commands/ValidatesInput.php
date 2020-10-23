<?php

namespace Slides\Saml2\Commands;

/**
 * Trait ValidatesInput
 *
 * @package Slides\Saml2\Commands
 */
trait ValidatesInput
{
    /**
     * Resolve the nameIdFormat.
     *
     * @param string $option
     *
     * @return string|null
     */
    protected function resolveNameIdFormat(string $option = 'nameIdFormat')
    {
        $value = $this->option($option) ?: 'persistent';

        if ($this->validateNameIdFormat($value)) {
            return $value;
        }

        $this->error('Name ID format is invalid. Supported values: ' . implode(', ', $this->supportedNameIdFormats()));

        return null;
    }

    /**
     * Validate Name ID format.
     *
     * @param string $format
     *
     * @return bool
     */
    protected function validateNameIdFormat(string $format): bool
    {
        return in_array($format, $this->supportedNameIdFormats());
    }

    /**
     * The list of supported Name ID formats.
     *
     * See https://docs.oracle.com/cd/E19316-01/820-3886/6nfcvtepi/index.html
     *
     * @return string[]|array
     */
    protected function supportedNameIdFormats(): array
    {
        return [
            'persistent',
            'transient',
            'emailAddress',
            'unspecified',
            'X509SubjectName',
            'WindowsDomainQualifiedName',
            'kerberos',
            'entity'
        ];
    }
}