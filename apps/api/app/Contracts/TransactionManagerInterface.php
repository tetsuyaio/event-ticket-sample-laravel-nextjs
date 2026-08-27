<?php

namespace App\Contracts;

use Closure;

interface TransactionManagerInterface
{
    /**
     * @template T
     *
     * @param  Closure(): T  $callback
     * @return T
     */
    public function run(Closure $callback, int $attempts = 1): mixed;
}
