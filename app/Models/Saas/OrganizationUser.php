<?php

namespace App\Models\Saas;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrganizationUser extends Pivot
{
    use SoftDeletes;

    protected $table = 'organization_user';

    public $timestamps = true;

    public $incrementing = false; // PK composta (organization_id + user_id)

    protected $fillable = [
        'organization_id',
        'user_id',
        'status',
        'is_owner',
        'is_admin',
        'role_hint',
        'panels',
    ];

    protected $casts = [
        'is_owner'   => 'bool',
        'is_admin'   => 'bool',
        'panels'     => 'array',
        'deleted_at' => 'datetime',
    ];

    // --- Relationships de conveniência ---

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return ($this->status ?? 'active') === 'active';
    }

    public function scopeOnlyActive($q)
    {
        return $q->where('status', 'active');
    }
}
