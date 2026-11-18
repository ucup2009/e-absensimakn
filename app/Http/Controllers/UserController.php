<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => $request->input('q'),
            'role' => $request->input('role'),
        ];

        $users = User::query()
            ->with('homeroomClass')
            ->when($filters['q'], fn ($q) => $q->where(function ($sub) use ($filters) {
                $sub->where('name', 'like', '%'.$filters['q'].'%')
                    ->orWhere('email', 'like', '%'.$filters['q'].'%')
                    ->orWhere('nip', 'like', '%'.$filters['q'].'%');
            }))
            ->when($filters['role'], fn ($q) => $q->where('role', $filters['role']))
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'filters' => $filters,
            'stats' => [
                'total' => User::count(),
                'admins' => User::where('role', User::ROLE_ADMIN)->count(),
                'teachers' => User::where('role', User::ROLE_WALI)->count(),
                'active' => User::where('is_active', true)->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('users.form', ['user' => new User(['role' => User::ROLE_WALI, 'is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active');
        $user = User::create($data);
        AuditLog::record('user.create', 'Menambah pengguna '.$user->name);

        return redirect()->route('users.index')->with('success', 'Pengguna '.$user->name.' berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('users.form', ['user' => $user]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validated($request, $user);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $data['is_active'] = $request->boolean('is_active');
        $user->update($data);
        AuditLog::record('user.update', 'Memperbarui pengguna '.$user->name);

        return redirect()->route('users.index')->with('success', 'Pengguna '.$user->name.' berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_if($request->user()->id === $user->id, 422, 'Anda tidak dapat menghapus akun Anda sendiri.');

        $name = $user->name;
        $user->delete();
        AuditLog::record('user.delete', 'Menghapus pengguna '.$name);

        return back()->with('success', 'Pengguna '.$name.' berhasil dihapus.');
    }

    protected function validated(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user?->id)],
            'role' => ['required', 'in:admin,wali_kelas'],
            'nip' => ['nullable', 'string', 'max:30'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }
}
