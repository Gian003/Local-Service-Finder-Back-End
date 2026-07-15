<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Worker;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
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
        $this->validateDatabaseConfiguration();

        Relation::enforceMorphMap([
            'user' => User::class,
            'worker' => Worker::class,
        ]);
    }

    /**
     * Validate critical database configuration on startup.
     */
    private function validateDatabaseConfiguration(): void
    {
        $connection = config('database.default');

        if (in_array($connection, ['mysql', 'pgsql', 'mariadb', 'sqlsrv'])) {
            $required = ['DB_HOST', 'DB_DATABASE', 'DB_USERNAME'];
            $missing = [];

            foreach ($required as $var) {
                if (!env($var)) {
                    $missing[] = $var;
                }
            }

            if (!empty($missing)) {
                $vars = implode(', ', $missing);
                throw new \RuntimeException(
                    "Database configuration incomplete. Missing: {$vars}. "
                    . "Please check your .env file for database connection details."
                );
            }
        }
    }
}
