<?php

namespace App\Models\Saas;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PlatformRoleUser extends Pivot
{
    protected $table = 'platform_role_user';

    public $timestamps = true;

    public $incrementing = false; // PK composta (role_id + user_id)

    protected $fillable = [
        'role_id',
        'user_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'bool',
    ];
}
