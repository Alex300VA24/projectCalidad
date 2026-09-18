<?php

namespace App\Providers;

use App\Models\CourseExecutionReport;
use App\Models\GraduateRegistry;
use App\Models\IncidenciaMatricula;
use App\Models\Matricula;
use App\Models\StudentReferral;
use App\Models\Syllabus;
use App\Models\TutoringSession;
use App\Observers\IndicadoresFuenteObserver;
use Illuminate\Database\Eloquent\Model;
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
        Model::preventLazyLoading(! app()->isProduction());

        foreach ([
            Matricula::class,
            IncidenciaMatricula::class,
            Syllabus::class,
            CourseExecutionReport::class,
            TutoringSession::class,
            StudentReferral::class,
            GraduateRegistry::class,
        ] as $model) {
            $model::observe(IndicadoresFuenteObserver::class);
        }
    }
}
