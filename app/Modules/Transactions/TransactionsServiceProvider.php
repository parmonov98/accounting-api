<?php

namespace App\Modules\Transactions;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Modules\Transactions\Contracts\TransactionRepositoryInterface;
use App\Modules\Transactions\Contracts\TransactionServiceInterface;
use App\Modules\Transactions\Models\Transaction;
use App\Modules\Transactions\Policies\TransactionPolicy;
use App\Modules\Transactions\Repositories\TransactionRepository;
use App\Modules\Transactions\Services\TransactionService;

class TransactionsServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Transaction::class => TransactionPolicy::class,
    ];

    public function register()
    {
        $this->app->bind(TransactionRepositoryInterface::class, TransactionRepository::class);
        $this->app->bind(TransactionServiceInterface::class, TransactionService::class);
    }

    public function boot()
    {
        $this->registerPolicies();

        // Register module routes and migrations
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');

        // Add a global scope to the Transaction model
        Transaction::addGlobalScope('author', function ($builder) {
            if (auth()->check()) {
                $builder->where('author_id', auth()->id());
            }
        });
    }
}
