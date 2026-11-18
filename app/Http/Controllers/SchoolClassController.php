<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SchoolClassController extends Controller
{
    public function index(): View
    {
        $classes = SchoolClass::query()
            ->with('homeroomTeacher')
            ->withCount('students')
            ->orderBy('level')
            ->orderBy('name')
            ->paginate(10);

        return view('classes.index', [
            'classes' => $classes,
            'teachers' => User::where('role', User::ROLE_WALI)->orderBy('name')->get(),
            'stats' => [
                'classes' => SchoolClass::count(),
                'students' => Student::where('is_active', true)->count(),
                'withHomeroom' => SchoolClass::whereNotNull('homeroom_teacher_id')->count(),
                'departments' => SchoolClass::query()->distinct()->count('department'),
            ],
        ]);
    }

    public function create(): View
    {
        return view('classes.form', [
            'class' => new SchoolClass(['level' => 'X', 'academic_year' => '2025/2026', 'capacity' => 36]),
            'teachers' => User::where('role', User::ROLE_WALI)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $class = SchoolClass::create($data);
        AuditLog::record('class.create', 'Menambah kelas '.$class->name);

        return redirect()->route('classes.index')->with('success', 'Kelas '.$class->name.' berhasil ditambahkan.');
    }

    public function edit(SchoolClass $class): View
    {
        return view('classes.form', [
            'class' => $class,
            'teachers' => User::where('role', User::ROLE_WALI)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, SchoolClass $class): RedirectResponse
    {
        $class->update($this->validated($request, $class));
        AuditLog::record('class.update', 'Memperbarui kelas '.$class->name);

        return redirect()->route('classes.index')->with('success', 'Kelas '.$class->name.' berhasil diperbarui.');
    }

    public function destroy(SchoolClass $class): RedirectResponse
    {
        $name = $class->name;
        $class->delete();
        AuditLog::record('class.delete', 'Menghapus kelas '.$name);

        return back()->with('success', 'Kelas '.$name.' berhasil dihapus. Siswa pada kelas ini menjadi tanpa kelas.');
    }

    protected function validated(Request $request, ?SchoolClass $class = null): array
    {
        return $request->validate([
            'name' => [
                'required', 'string', 'max:60',
                Rule::unique('classes', 'name')
                    ->where('academic_year', $request->input('academic_year'))
                    ->ignore($class?->id),
            ],
            'level' => ['required', 'in:X,XI,XII'],
            'department' => ['required', 'string', 'max:40'],
            'homeroom_teacher_id' => ['nullable', 'exists:users,id'],
            'academic_year' => ['required', 'string', 'max:20'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:60'],
        ]);
    }
}
