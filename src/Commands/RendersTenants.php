<?php

namespace Slides\Saml2\Commands;

/**
 * Class CreateTenant
 *
 * @package Slides\Saml2\Commands
 */
trait RendersTenants
{
    /**
     * Render tenants in a table.
     *
     * @param \Slides\Saml2\Models\Tenant|\Illuminate\Support\Collection $tenants
     * @param string|null $title
     *
     * @return void
     */
    protected function renderTenants($tenants, string $title = null)
    {
        /** @var \Slides\Saml2\Models\Tenant[]|\Illuminate\Database\Eloquent\Collection $tenants */
        $tenants = $tenants instanceof \Slides\Saml2\Models\Tenant
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
     * Get a columns of the Tenant.
     *
     * @param \Slides\Saml2\Models\Tenant $tenant
     *
     * @return array
     */
    protected function getTenantColumns(\Slides\Saml2\Models\Tenant $tenant)
    {
        return [
            'ID' => $tenant->id,
            'UUID' => $tenant->uuid,
            'Key' => $tenant->key,
            'Entity ID' => $tenant->idp_entity_id,
            'Login URL' => $tenant->idp_login_url,
            'Logout URL' => $tenant->idp_logout_url,
            'Metadata' => $this->renderArray($tenant->metadata ?: []),
            'Created' => $tenant->created_at->toDateTimeString(),
            'Updated' => $tenant->updated_at->toDateTimeString(),
            'Deleted' => $tenant->deleted_at ? $tenant->deleted_at->toDateTimeString() : null
        ];
    }

    /**
     * Print an array to a string.
     *
     * @param array $array
     *
     * @return string
     */
    protected function renderArray(array $array)
    {
        $lines = [];

        foreach ($array as $key => $value) {
            $lines[] = "$key: $value";
        }

        return implode(PHP_EOL, $lines);
    }
}