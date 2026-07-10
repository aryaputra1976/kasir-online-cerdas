<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q'));
        $role = $request->input('role');
        $status = $request->input('status');

        $usersQuery = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%');
                });
            })
            ->when($role, fn ($query) => $query->where('role', $role))
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false));

        $users = (clone $usersQuery)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $inactiveUsers = User::where('is_active', false)->count();
        $ownerUsers = User::where('role', User::ROLE_OWNER)->count();
        $adminUsers = User::where('role', User::ROLE_ADMIN)->count();
        $kasirUsers = User::where('role', User::ROLE_KASIR)->count();

        $roleOptions = User::roleOptions();

        return view('users-list', compact(
            'users',
            'search',
            'role',
            'status',
            'totalUsers',
            'activeUsers',
            'inactiveUsers',
            'ownerUsers',
            'adminUsers',
            'kasirUsers',
            'roleOptions'
        ));
    }

    public function create(): View
    {
        $user = new User([
            'role' => User::ROLE_KASIR,
            'is_active' => true,
        ]);

        return view('add-user', [
            'user' => $user,
            'mode' => 'create',
            'roleOptions' => User::roleOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateUser($request);

        User::create($validated);

        return redirect()
            ->route('settings.users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('add-user', [
            'user' => $user,
            'mode' => 'edit',
            'roleOptions' => User::roleOptions(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $this->validateUser($request, $user);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()
            ->route('settings.users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if (User::count() <= 1) {
            return redirect()
                ->route('settings.users.index')
                ->with('error', 'User terakhir tidak boleh dihapus.');
        }

        $user->delete();

        return redirect()
            ->route('settings.users.index')
            ->with('success', 'User berhasil dihapus.');
    }

    private function validateUser(Request $request, ?User $user = null): array
    {
        $isEdit = $user !== null;

        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', Rule::in(array_keys(User::roleOptions()))],
            'is_active' => ['required', 'boolean'],
            'password' => [
                $isEdit ? 'nullable' : 'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ], [
            'name.required' => 'Nama user wajib diisi.',
            'email.required' => 'Email user wajib diisi.',
            'email.unique' => 'Email sudah digunakan oleh user lain.',
            'role.required' => 'Role user wajib dipilih.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
        ]);
    }
}