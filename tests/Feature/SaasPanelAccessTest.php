<?php

namespace Tests\Feature;

use App\Models\Saas\PlatformRole;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SaasPanelAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('platform_roles', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('platform_role_user', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('user_id');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->primary(['role_id', 'user_id']);
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('platform_role_user');
        Schema::dropIfExists('platform_roles');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    /**
     * @dataProvider allowedPlatformRoles
     */
    public function test_user_with_platform_role_can_access_saas(string $role): void
    {
        $user = User::factory()->create();

        $platformRole = PlatformRole::create([
            'slug' => $role,
            'name' => ucfirst(str_replace('_', ' ', $role)),
        ]);

        $user->platformRoles()->attach($platformRole, ['is_active' => true]);

        $response = $this->actingAs($user)->get('/saas');

        $response->assertStatus(200);
    }

    public static function allowedPlatformRoles(): array
    {
        return [
            ['platform_owner'],
            ['support_agent'],
            ['account_manager'],
            ['billing_admin'],
            ['readonly'],
        ];
    }

    public function test_user_without_platform_role_cannot_access_saas(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/saas');

        $response->assertStatus(403);
    }
}
