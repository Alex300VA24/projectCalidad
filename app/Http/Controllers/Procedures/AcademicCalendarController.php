<?php

namespace App\Http\Controllers\Procedures;

use App\Http\Controllers\Controller;
use App\Models\AcademicCalendar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AcademicCalendarController extends Controller
{
    public function index(Request $request): View
    {
        return view('tramites.academic-calendars.index', [
            'calendars' => AcademicCalendar::query()->latest('id')->get(),
            'editing' => $request->integer('edit') ? AcademicCalendar::find($request->integer('edit')) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        AcademicCalendar::create($this->prepare($request->validate($this->rules($request))));

        return redirect()->route('academic-calendars.index')->with('success', 'Calendario académico registrado correctamente.');
    }

    public function update(Request $request, AcademicCalendar $academicCalendar): RedirectResponse
    {
        $academicCalendar->update($this->prepare($request->validate($this->rules($request, $academicCalendar))));

        return redirect()->route('academic-calendars.index')->with('success', 'Calendario académico actualizado correctamente.');
    }

    public function destroy(AcademicCalendar $academicCalendar): RedirectResponse
    {
        $academicCalendar->delete();

        return back()->with('success', 'Calendario académico eliminado.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(Request $request, ?AcademicCalendar $academicCalendar = null): array
    {
        return [
            'academic_period' => ['required', 'string', 'max:20', Rule::unique('academic_calendars', 'academic_period')->ignore($academicCalendar?->id)],
            'activities_schedule' => ['nullable', 'json'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function prepare(array $validated): array
    {
        $validated['activities_schedule'] = ! empty($validated['activities_schedule']) ? json_decode($validated['activities_schedule'], true) : null;

        return $validated;
    }
}
