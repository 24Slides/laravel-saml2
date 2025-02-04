<?php

namespace Slides\Saml2\Commands;

use Slides\Saml2\Contracts\IdentityProvidable;
use Illuminate\Support\Str;

trait RendersTenants
{
    /**
     * Render tenants in a table.
     *
     * @param IdentityProvidable|\Illuminate\Support\Collection $tenants
     * @param string|null $title
     *
     * @return void
     */
    protected function renderTenants($tenants, ?string $title = null)
    {
        /** @var \Slides\Saml2\Models\IdentityProvider[]|\Illuminate\Database\Eloquent\Collection $tenants */
        $idps = $tenants instanceof IdentityProvidable
            ? collect([$tenants])
            : $tenants;

        $headers = ['Column', 'Value'];
        $columns = [];

        foreach ($tenants as $tenant) {
            foreach ($this->getTenantColumns($tenant) as $column => $value) {
                $columns[] = [$column, $value ?: '(empty)'];
            }

            if($tenants->last()->id !== $tenant->id) {
                $columns[] = new \Symfony\Component\Console\Helper\TableSeparator();
            }
        }

        if($title) {
            $this->getOutput()->title($title);
        }

        $this->table($headers, $columns);
    }

    /**
     * Get a columns of the IdentityProvidable.
     *
     * @param IdentityProvidable $tenant
     *
     * @return array
     */
    protected function getTenantColumns(IdentityProvidable $tenant)
    {
        return [
            'ID' => $tenant->id,
            'UUID' => $tenant->uuid,
            'Key' => $tenant->key,
            'Entity ID' => $tenant->idp_entity_id,
            'Login URL' => $tenant->idp_login_url,
            'Logout URL' => $tenant->idp_logout_url,
            'Relay State URL' => $tenant->relay_state_url,
            'Name ID format' => $tenant->name_id_format,
            'x509 cert' => Str::limit($tenant->idp_x509_cert, 50),
            'Metadata' => $this->renderArray($tenant->metadata ?: []),
            'Created' => $tenant->created_at->toDateTimeString(),
            'Updated' => $tenant->updated_at->toDateTimeString(),
            'Deleted' => $tenant->deleted_at ? $tenant->deleted_at->toDateTimeString() : null
        ];
    }

    /**
     * Render IDP credentials.
     *
     * @param IdentityProvidable $idp
     *
     * @return void
     */
    protected function renderTenantCredentials(IdentityProvidable $idp)
    {
        $this->output->section('Identity Provider credentials');

        $this->getOutput()->text([
            'Identifier (Entity ID): <comment>' . route('saml.metadata', ['uuid' => $idp->uuid]) . '</comment>',
            'Reply URL (Assertion Consumer Service URL): <comment>' . route('saml.acs', ['uuid' => $idp->uuid]) . '</comment>',
            'Sign on URL: <comment>' . route('saml.login', ['uuid' => $idp->uuid]) . '</comment>',
            'Logout URL: <comment>' . route('saml.logout', ['uuid' => $idp->uuid]) . '</comment>',
            'Relay State: <comment>' . ($idp->relay_state_url ?: config('saml2.loginRoute')) . ' (optional)</comment>'
        ]);
    }

    /**
     * Print an array to a string.
     *
     * @param array $array
     *
     * @return string
     */
    protected function renderArray(array $array): string
    {
        $lines = [];

        foreach ($array as $key => $value) {
            $lines[] = "$key: $value";
        }

        return implode(PHP_EOL, $lines);
    }
}
