<?php

namespace App\Infrastructure;

use App\Contracts\TransactionManagerInterface;
use Closure;
use Illuminate\Support\Facades\DB;

final class DatabaseTransactionManager implements TransactionManagerInterface
{
    public function run(Closure $callback, int $attempts = 1): mixed
    {
        return DB::transaction($callback, $attempts);
    }
}
