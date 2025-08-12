<?php

namespace App\Models\Saas;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountUser extends Pivot
{
    use SoftDeletes;

    protected $table = 'account_user';

    public $timestamps = true;

    public $incrementing = false; // PK composta (account_id + user_id)

    protected $fillable = [
        'account_id',
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

    public function account()
    {
        return $this->belongsTo(Account::class);
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
