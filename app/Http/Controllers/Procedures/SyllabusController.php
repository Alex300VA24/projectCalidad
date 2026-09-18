<?php

namespace App\Http\Controllers\Procedures;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\PeriodoAcademico;
use App\Models\ProgramaEstudio;
use App\Models\Syllabus;
use App\Models\User;
use App\Services\Procedures\CurriculumManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SyllabusController extends Controller
{
    public function index(Request $request): View
    {
        return view('tramites.syllabi.index', [
            'syllabi' => Syllabus::query()->with(['course', 'teacher'])->latest('id')->get(),
            'courses' => Course::query()->orderBy('name')->get(),
            'programas' => ProgramaEstudio::query()->where('activo', true)->orderBy('nombre')->get(),
            'periodos' => PeriodoAcademico::query()->where('activo', true)->orderByDesc('codigo')->get(),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
            'editing' => $request->integer('edit') ? Syllabus::find($request->integer('edit')) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->prepare($request->validate($this->rules()));
        abort_if($datos['status'] === 'visado' && ! ($request->user()?->can('syllabus.vise') ?? false), 403);
        Syllabus::create($datos);

        return redirect()->route('syllabi.index')->with('success', 'Sílabo registrado correctamente.');
    }

    public function update(Request $request, Syllabus $syllabus, CurriculumManagementService $curriculumManagement): RedirectResponse
    {
        $datos = $this->prepare($request->validate($this->rules()));
        abort_if($datos['status'] === 'visado' && ! ($request->user()?->can('syllabus.vise') ?? false), 403);

        if ($datos['status'] === 'visado' && $syllabus->status !== 'visado') {
            $checklist = $datos['review_checklist'] ?? [];
            unset($datos['status'], $datos['review_checklist']);
            $syllabus->update($datos);
            $curriculumManagement->viseSyllabus($syllabus, $checklist);
        } else {
            $syllabus->update($datos);
        }

        return redirect()->route('syllabi.index')->with('success', 'Sílabo actualizado correctamente.');
    }

    public function destroy(Syllabus $syllabus): RedirectResponse
    {
        $syllabus->delete();

        return back()->with('success', 'Sílabo eliminado.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'programa_estudio_id' => ['required', 'integer', 'exists:programas_estudio,id'],
            'periodo_academico_id' => ['required', 'integer', 'exists:periodos_academicos,id'],
            'teacher_id' => ['required', 'integer', 'exists:users,id'],
            'academic_period' => ['nullable', 'string', 'max:20'],
            'status' => ['required', Rule::in(['draft', 'submitted', 'reviewed', 'visado'])],
            'review_checklist' => ['nullable', 'json'],
            'library_requirement_data' => ['nullable', 'json'],
            'tipo_silabo' => ['nullable', Rule::in(['objetivos', 'competencias'])],
            'modalidad' => ['nullable', Rule::in(['presencial', 'semipresencial', 'no_presencial'])],
            'seccion' => ['nullable', 'string', 'max:20'],
            'fundamentacion' => ['nullable', 'string'],
            'aprendizajes_esperados' => ['nullable', 'string'],
            'unidades' => ['nullable', 'array'],
            'unidades.*.denominacion' => ['nullable', 'string', 'max:255'],
            'unidades.*.objetivos' => ['nullable', 'string'],
            'unidades.*.contenidos' => ['nullable', 'string'],
            'unidades.*.evaluacion' => ['nullable', 'string'],
            'sesiones_no_presenciales' => ['nullable', 'array'],
            'sesiones_no_presenciales.*.semana' => ['nullable', 'string', 'max:20'],
            'sesiones_no_presenciales.*.sesion' => ['nullable', 'string', 'max:20'],
            'sesiones_no_presenciales.*.nombre' => ['nullable', 'string', 'max:255'],
            'sesiones_no_presenciales.*.objetivo' => ['nullable', 'string'],
            'guias_aprendizaje' => ['nullable', 'string'],
            'material_trabajo_distancia' => ['nullable', 'string'],
            'tutoria_dia' => ['nullable', 'string', 'max:60'],
            'tutoria_medio' => ['nullable', 'string', 'max:120'],
            'tutoria_horario' => ['nullable', 'string', 'max:60'],
            'bibliografia' => ['nullable', 'string'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function prepare(array $validated): array
    {
        $validated['academic_period'] = PeriodoAcademico::query()->findOrFail($validated['periodo_academico_id'])->codigo;
        foreach (['review_checklist', 'library_requirement_data'] as $field) {
            $validated[$field] = ! empty($validated[$field]) ? json_decode($validated[$field], true) : null;
        }
        foreach (['unidades', 'sesiones_no_presenciales'] as $field) {
            $validated[$field] = collect($validated[$field] ?? [])
                ->filter(fn (array $fila) => collect($fila)->filter(fn ($valor) => filled($valor))->isNotEmpty())
                ->values()->all();
            if (empty($validated[$field])) {
                $validated[$field] = null;
            }
        }

        return $validated;
    }
}
