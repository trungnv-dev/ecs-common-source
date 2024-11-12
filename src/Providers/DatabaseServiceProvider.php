<?php

namespace App\Providers;

use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->queryLog();
    }

    private function queryLog()
    {
        if (config('app.enable_sql_log')) {
            $channel = 'mysql';

            DB::listen(function (QueryExecuted $query) use ($channel) {
                $sql = $query->sql;
                foreach ($query->bindings as $binding) {
                    if (is_string($binding)) {
                        $binding = "'{$binding}'";
                    } elseif ($binding === null) {
                        $binding = 'NULL';
                    } elseif ($binding instanceof Carbon) {
                        $binding = "'{$binding->toDateTimeString()}'";
                    } elseif ($binding instanceof DateTime) {
                        $binding = "'{$binding->format('Y-m-d H:i:s')}'";
                    }

                    $sql = preg_replace("/\?/", $binding, $sql, 1);
                }

                Log::channel($channel)->debug('SQL::', ['sql' => $sql, 'time' => "$query->time ms"]);
            });

            Event::listen(TransactionBeginning::class, function () use ($channel) {
                Log::channel($channel)->debug('START TRANSACTION');
            });

            Event::listen(TransactionCommitted::class, function () use ($channel) {
                Log::channel($channel)->debug('COMMIT');
            });

            Event::listen(TransactionRolledBack::class, function () use ($channel) {
                Log::channel($channel)->debug('ROLLBACK');
            });
        }
    }
}
