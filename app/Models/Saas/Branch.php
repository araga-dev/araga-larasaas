<?php

namespace App\Models\Saas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'branches';

    protected $fillable = [
        'account_id',
        'name',
        'code',
        'parent_id',
        'is_primary',   // <-- adicionar
        'is_active',
    ];

    protected $casts = [
        'is_active'  => 'bool',
        'is_primary' => 'bool',   // <-- adicionar
        'deleted_at' => 'datetime',
    ];

    // --- Relationships ---
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function parent()
    {
        return $this->belongsTo(Branch::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Branch::class, 'parent_id');
    }

    // --- Scopes úteis ---
    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
    public function scopeRoots($q)
    {
        return $q->whereNull('parent_id');
    }
    public function scopePrimary($q)
    {
        return $q->where('is_primary', 1);
    }
    public function scopeByCode($q, $c)
    {
        return $q->where('code', $c);
    }

    // --- Guard-rails leves na aplicação ---
    protected static function booted(): void
    {
        static::saving(function (Branch $b) {
            // normaliza: 0/false -> null (evita colisão no UNIQUE (account_id, is_primary))
            if (! $b->is_primary) {
                $b->is_primary = null;
            }

            // se tiver pai, precisa ser da mesma conta
            if ($b->parent_id) {
                $parent = static::query()->select('account_id')->find($b->parent_id);
                if (! $parent || $parent->account_id !== (int) $b->account_id) {
                    throw new \RuntimeException('Parent branch must belong to the same account.');
                }
            }
        });
    }

    // helper
    public function isDefault(): bool
    {
        return (bool) $this->is_primary;
    }
}
