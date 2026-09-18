<?php

namespace App\Observers;

use App\Services\IndicadoresCacheService;
use Illuminate\Database\Eloquent\Model;

class IndicadoresFuenteObserver
{
    public function __construct(private IndicadoresCacheService $cache) {}

    public function created(Model $model): void
    {
        $this->cache->olvidarModelo($model);
    }

    public function updated(Model $model): void
    {
        $this->cache->olvidarModelo($model);
    }

    public function deleted(Model $model): void
    {
        $this->cache->olvidarModelo($model);
    }

    public function restored(Model $model): void
    {
        $this->cache->olvidarModelo($model);
    }

    public function forceDeleted(Model $model): void
    {
        $this->cache->olvidarModelo($model);
    }
}
