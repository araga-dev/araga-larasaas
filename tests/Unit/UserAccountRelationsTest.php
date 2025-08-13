<?php

namespace Tests\Unit;

use App\Models\Saas\Account;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UserAccountRelationsTest extends TestCase
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

        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('account_user', function (Blueprint $table) {
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('user_id');
            $table->boolean('is_owner')->default(false);
            $table->boolean('is_admin')->default(false);
            $table->string('status')->default('active');
            $table->string('role_hint')->nullable();
            $table->json('panels')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
            $table->primary(['account_id', 'user_id']);
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('account_user');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_user_account_relations_are_classified_correctly(): void
    {
        $user = User::factory()->create();

        $owner = Account::create(['name' => 'Owner Co', 'slug' => 'owner']);
        $admin = Account::create(['name' => 'Admin Co', 'slug' => 'admin']);
        $member = Account::create(['name' => 'Member Co', 'slug' => 'member']);

        $user->accounts()->attach($owner->id, ['is_owner' => true, 'is_admin' => false, 'status' => 'active']);
        $user->accounts()->attach($admin->id, ['is_owner' => false, 'is_admin' => true, 'status' => 'active']);
        $user->accounts()->attach($member->id, ['is_owner' => false, 'is_admin' => false, 'status' => 'active']);

        $user->load('ownedAccounts', 'adminAccounts', 'memberAccounts');

        $this->assertTrue($user->ownedAccounts->contains($owner));
        $this->assertTrue($user->adminAccounts->contains($admin));
        $this->assertTrue($user->memberAccounts->contains($member));
    }
}
