<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => $request->input('q'),
            'class_id' => $request->input('class_id'),
            'gender' => $request->input('gender'),
            'qr' => $request->input('qr'),
            'active' => $request->input('active'),
        ];

        $students = Student::query()
            ->with('schoolClass')
            ->search($filters['q'])
            ->when($filters['class_id'], fn ($q) => $q->where('class_id', $filters['class_id']))
            ->when($filters['gender'], fn ($q) => $q->where('gender', $filters['gender']))
            ->when($filters['qr'] === 'ready', fn ($q) => $q->whereNotNull('qr_code'))
            ->when($filters['qr'] === 'missing', fn ($q) => $q->whereNull('qr_code'))
            ->when($filters['active'] !== null && $filters['active'] !== '', fn ($q) => $q->where('is_active', (bool) $filters['active']))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $allActive = Student::where('is_active', true);

        return view('students.index', [
            'students' => $students,
            'classes' => SchoolClass::orderBy('name')->get(),
            'filters' => $filters,
            'stats' => [
                'total' => (clone $allActive)->count(),
                'male' => (clone $allActive)->where('gender', 'L')->count(),
                'female' => (clone $allActive)->where('gender', 'P')->count(),
                'qr' => (clone $allActive)->whereNotNull('qr_code')->count(),
                'wa' => (clone $allActive)->where('parent_phone', 'like', '62%')->count(),
                'classes' => SchoolClass::count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('students.form', [
            'student' => new Student(['is_active' => true, 'gender' => 'L']),
            'classes' => SchoolClass::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['qr_code'] = $this->uniqueQrCode($data['nisn']);
        $data['qr_generated_at'] = now();
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('students', 'public');
        }

        $student = Student::create($data);
        AuditLog::record('student.create', 'Menambah siswa '.$student->name.' ('.$student->nisn.')');

        return redirect()->route('students.index')->with('success', 'Data siswa '.$student->name.' berhasil ditambahkan.');
    }

    public function edit(Student $student): View
    {
        return view('students.form', [
            'student' => $student,
            'classes' => SchoolClass::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $data = $this->validated($request, $student);
        $data['is_active'] = $request->boolean('is_active');

        if ($student->nisn !== $data['nisn']) {
            $data['qr_code'] = $this->uniqueQrCode($data['nisn']);
            $data['qr_generated_at'] = now();
        }

        if ($request->hasFile('photo')) {
            if ($student->photo) {
                Storage::disk('public')->delete($student->photo);
            }
            $data['photo'] = $request->file('photo')->store('students', 'public');
        }

        $student->update($data);
        AuditLog::record('student.update', 'Memperbarui data siswa '.$student->name);

        return redirect()->route('students.index')->with('success', 'Data siswa '.$student->name.' berhasil diperbarui.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        if ($student->photo) {
            Storage::disk('public')->delete($student->photo);
        }

        $name = $student->name;
        $student->delete();
        AuditLog::record('student.delete', 'Menghapus siswa '.$name);

        return back()->with('success', 'Data siswa '.$name.' berhasil dihapus.');
    }

    public function card(Student $student): View
    {
        return view('students.card', [
            'student' => $student->load('schoolClass'),
            'qr' => QrCode::format('svg')->size(220)->margin(1)->generate($student->qrPayload()),
        ]);
    }

    public function printCards(Request $request): View
    {
        $request->validate([
            'ids' => ['nullable', 'array'],
            'ids.*' => ['integer', 'exists:students,id'],
            'class_id' => ['nullable', 'exists:classes,id'],
        ]);

        $students = Student::query()
            ->with('schoolClass')
            ->where('is_active', true)
            ->when($request->filled('ids'), fn ($q) => $q->whereIn('id', $request->input('ids', [])))
            ->when(! $request->filled('ids') && $request->filled('class_id'), fn ($q) => $q->where('class_id', $request->class_id))
            ->orderBy('name')
            ->get();

        return view('students.cards', compact('students'));
    }

    protected function validated(Request $request, ?Student $student = null): array
    {
        $data = $request->validate([
            'nisn' => ['required', 'digits:10', Rule::unique('students', 'nisn')->ignore($student?->id)],
            'name' => ['required', 'string', 'max:120'],
            'class_id' => ['required', 'exists:classes,id'],
            'gender' => ['required', 'in:L,P'],
            'parent_name' => ['nullable', 'string', 'max:120'],
            'parent_phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        $data['parent_phone'] = $this->normalizePhone($data['parent_phone'] ?? null);

        if ($data['parent_phone'] && ! preg_match('/^62\d{8,14}$/', $data['parent_phone'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'parent_phone' => 'Nomor WhatsApp orang tua harus diawali 62 (contoh: 6281234567890).',
            ]);
        }

        unset($data['photo']);

        return $data;
    }

    protected function normalizePhone(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        if (str_starts_with($digits, '8')) {
            return '62'.$digits;
        }

        return $digits;
    }

    protected function uniqueQrCode(string $nisn): string
    {
        $base = 'MAKN-'.$nisn;

        if (! Student::where('qr_code', $base)->exists()) {
            return $base;
        }

        return $base.'-'.Str::upper(Str::random(4));
    }
}
