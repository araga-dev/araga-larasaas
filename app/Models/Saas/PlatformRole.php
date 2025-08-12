<?php

namespace App\Models\Saas;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformRole extends Model
{
    use HasFactory;

    protected $table = 'platform_roles';

    protected $fillable = [
        'slug',
        'name',
        'description',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'platform_role_user', 'role_id', 'user_id')
            ->using(PlatformRoleUser::class)
            ->withPivot(['is_active', 'created_at', 'updated_at'])
            ->withTimestamps();
    }
}
