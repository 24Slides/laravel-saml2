<?php

namespace Slides\Saml2\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Slides\Saml2\Contracts\IdentityProvidable;

/**
 * @property int $id
 * @property string $uuid
 * @property string $key
 * @property string $idp_entity_id
 * @property string $idp_login_url
 * @property string $idp_logout_url
 * @property string $idp_x509_cert
 * @property string $relay_state_url
 * @property string $name_id_format
 * @property int|null $owner_id
 * @property string|null $owner_type
 * @property array $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 *
 * @property-read \Illuminate\Database\Eloquent\Model|null $tenant
 * @property-read \Illuminate\Database\Eloquent\Model $sessions
 */
class IdentityProvider extends Model implements IdentityProvidable
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'saml2_identity_providers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'key',
        'idp_entity_id',
        'idp_login_url',
        'idp_logout_url',
        'idp_x509_cert',
        'relay_state_url',
        'name_id_format',
        'tenant_id',
        'tenant_type',
        'metadata'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'metadata' => 'array'
    ];

    /**
     * @return string
     */
    public function idpUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function idpEntityId(): string
    {
        return $this->idp_entity_id;
    }

    /**
     * @return string
     */
    public function idpLoginUrl(): string
    {
        return $this->idp_login_url;
    }

    /**
     * @return string
     */
    public function idpLogoutUrl(): string
    {
        return $this->idp_logout_url;
    }

    /**
     * @return string
     */
    public function idpX509cert(): ?string
    {
        return $this->idp_x509_cert;
    }

    /**
     * @return string
     */
    public function idpNameIdFormat(): string
    {
        return $this->name_id_format;
    }

    /**
     * The tenant model.
     *
     * @return MorphTo
     */
    public function tenant(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The sessions of the tenant.
     *
     * @return HasMany
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }
}