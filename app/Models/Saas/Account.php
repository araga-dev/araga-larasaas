<?php

namespace App\Models\Saas;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'accounts';

    protected $fillable = [
        'name',
        'slug',
        'plan',
        'status',
        'primary_branch_id',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    // --- Relationships ---
    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function rootBranches()
    {
        return $this->hasMany(Branch::class)->whereNull('parent_id');
    }

    public function activeBranches()
    {
        return $this->hasMany(Branch::class)->where('is_active', true);
    }

    public function primaryBranch()
    {
        return $this->belongsTo(Branch::class, 'primary_branch_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'account_user')
            ->using(AccountUser::class)
            ->withPivot(['status', 'is_owner', 'is_admin', 'role_hint', 'panels', 'deleted_at', 'created_at', 'updated_at'])
            ->withTimestamps()
            ->wherePivotNull('deleted_at');
    }

    public function usersWithTrashedPivot()
    {
        return $this->belongsToMany(User::class, 'account_user')
            ->using(AccountUser::class)
            ->withPivot(['status', 'is_owner', 'is_admin', 'role_hint', 'panels', 'deleted_at', 'created_at', 'updated_at'])
            ->withTimestamps();
    }

    // --- Conveniências ---
    /** Define o branch padrão da conta (valida a propriedade) */
    public function setPrimaryBranch(Branch $branch): self
    {
        if ($branch->account_id !== $this->id) {
            throw new \InvalidArgumentException('O branch não pertence a esta account.');
        }
        $this->primary_branch_id = $branch->id;
        $this->save();

        // opcional: marcar o branch como primary (mantendo “solto”, sem obrigatoriedade)
        $branch->is_primary = true;
        $branch->save();

        return $this;
    }

    /** Retorna o branch padrão “efetivo” (relacionamento ou o único marcado como primary) */
    public function effectivePrimaryBranch(): ?Branch
    {
        if ($this->relationLoaded('primaryBranch') ? $this->primaryBranch : $this->primaryBranch()->exists()) {
            return $this->primaryBranch;
        }

        return $this->branches()->where('is_primary', 1)->first();
    }
}
