<?php

namespace Slides\Saml2\Repositories;

use Slides\Saml2\Models\Tenant;

class TenantRepository
{
    /**
     * Create a new query.
     *
     * @param bool $withTrashed Whether need to include safely deleted records.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(bool $withTrashed = false)
    {
        $class = config('saml2.tenantModel', Tenant::class);
        $query = $class::query();

        if($withTrashed) {
            $query->withTrashed();
        }

        return $query;
    }

    /**
     * Find all tenants.
     *
     * @param bool $withTrashed Whether need to include safely deleted records.
     *
     * @return Tenant[]|\Illuminate\Database\Eloquent\Collection
     */
    public function all(bool $withTrashed = true)
    {
        return $this->query($withTrashed)->get();
    }

    /**
     * Find a tenant by any identifier.
     *
     * @param int|string $key ID, key or UUID
     * @param bool $withTrashed Whether need to include safely deleted records.
     *
     * @return Tenant[]|\Illuminate\Database\Eloquent\Collection
     */
    public function findByAnyIdentifier($key, bool $withTrashed = true)
    {
        $query = $this->query($withTrashed);

        if (is_int($key)) {
            return $query->where('id', $key)->get();
        }

        return $query->where('key', $key)
            ->orWhere('uuid', $key)
            ->get();
    }

    /**
     * Find a tenant by the key.
     *
     * @param string $key
     * @param bool $withTrashed
     *
     * @return Tenant|\Illuminate\Database\Eloquent\Model|null
     */
    public function findByKey(string $key, bool $withTrashed = true)
    {
        return $this->query($withTrashed)
            ->where('key', $key)
            ->first();
    }

    /**
     * Find a tenant by ID.
     *
     * @param int $id
     * @param bool $withTrashed
     *
     * @return Tenant|\Illuminate\Database\Eloquent\Model|null
     */
    public function findById(int $id, bool $withTrashed = true)
    {
        return $this->query($withTrashed)
            ->where('id', $id)
            ->first();
    }

    /**
     * Find a tenant by UUID.
     *
     * @param int $uuid
     * @param bool $withTrashed
     *
     * @return Tenant|\Illuminate\Database\Eloquent\Model|null
     */
    public function findByUUID(string $uuid, bool $withTrashed = true)
    {
        return $this->query($withTrashed)
            ->where('uuid', $uuid)
            ->first();
    }
}
