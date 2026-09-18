<?php

namespace App\Http\Controllers\Procedures;

use App\Http\Controllers\Controller;
use App\Models\Curriculum;
use App\Models\CurriculumReview;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CurriculumReviewController extends Controller
{
    public function index(Request $request): View
    {
        return view('tramites.curriculum-reviews.index', [
            'reviews' => CurriculumReview::query()->with(['curriculum', 'coteccuUser'])->latest('id')->get(),
            'curricula' => Curriculum::query()->orderBy('name')->get(),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
            'editing' => $request->integer('edit') ? CurriculumReview::find($request->integer('edit')) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        CurriculumReview::create($this->prepare($request->validate($this->rules())));

        return redirect()->route('curriculum-reviews.index')->with('success', 'Revisión curricular registrada correctamente.');
    }

    public function update(Request $request, CurriculumReview $curriculumReview): RedirectResponse
    {
        $curriculumReview->update($this->prepare($request->validate($this->rules())));

        return redirect()->route('curriculum-reviews.index')->with('success', 'Revisión curricular actualizada correctamente.');
    }

    public function destroy(CurriculumReview $curriculumReview): RedirectResponse
    {
        $curriculumReview->delete();

        return back()->with('success', 'Revisión curricular eliminada.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'curriculum_id' => ['required', 'integer', 'exists:curricula,id'],
            'coteccu_user_id' => ['required', 'integer', 'exists:users,id'],
            'review_checklist_data' => ['nullable', 'json'],
            'technical_report_path' => ['nullable', 'string', 'max:255'],
            'decision' => ['nullable', Rule::in(['revalidar', 'ajustar', 'redisenar'])],
            'state' => ['required', Rule::in(['en_revision', 'aprobado', 'rechazado', 'completado'])],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function prepare(array $validated): array
    {
        $validated['review_checklist_data'] = ! empty($validated['review_checklist_data'])
            ? json_decode($validated['review_checklist_data'], true)
            : null;

        return $validated;
    }
}
