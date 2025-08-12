<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Saas\Account;
use App\Models\Saas\Policies\AccountPolicy;

class SaasServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Evita erro "Specified key was too long" em utf8mb4
        Schema::defaultStringLength(191);

        // Define InnoDB como engine padrão para todas as migrations
        # Schema::defaultTableEngine('InnoDB') -> não existe mais
        # config\database.php -> 'engine' =>  env('DB_ENGINE', null),
        # .env -> DB_ENGINE=InnoDB

        Gate::policy(Account::class, AccountPolicy::class);
    }
}
