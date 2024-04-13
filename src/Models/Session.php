<?php

namespace Slides\Saml2\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @property int $id
 * @property int $idp_id
 * @property int|null $user_id
 * @property array $payload
 * @property \Carbon\Carbon $created_at
 *
 * @property-read IdentityProvider|null $tenant
 * @property-read Authenticatable|Model $user
 */
class Session extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'saml2_sessions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'idp_id',
        'payload',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'payload' => 'array'
    ];

    /**
     * The user model.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('saml2.auth.userModel'));
    }

    /**
     * The tenant model (identity provider).
     *
     * @return HasOne
     */
    public function tenant(): HasOne
    {
        return $this->hasOne(config('saml2.idpModel'), 'id', 'idp_id');
    }
}
