<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    /**
     * Display a listing of the administrator accounts.
     */
    public function index(Request $request): View
    {
        $admins = User::query()
            ->where('role', 'admin')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search')->trim()->value();

                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('admins'));
    }

    /**
     * Show the form for creating a new administrator.
     */
    public function create(): View
    {
        $admin = new User([
            'role' => 'admin',
            'is_active' => true,
        ]);

        return view('admin.users.create', compact('admin'));
    }

    /**
     * Store a newly created administrator.
     */
    public function store(AdminUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['role'] = 'admin';

        User::create($validated);

        return redirect()
            ->route('admin.admins.index')
            ->with('success', 'Akun admin berhasil dibuat.');
    }

    /**
     * Show the form for editing the given administrator.
     */
    public function edit(User $admin): View
    {
        $this->ensureAdminAccount($admin);

        return view('admin.users.edit', compact('admin'));
    }

    /**
     * Update the given administrator.
     */
    public function update(AdminUserRequest $request, User $admin): RedirectResponse
    {
        $this->ensureAdminAccount($admin);

        $validated = $request->validated();

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        if ($admin->is($request->user()) && ! $validated['is_active']) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        if (! $validated['is_active'] && $this->isLastActiveAdmin($admin)) {
            return back()->with('error', 'Minimal harus ada satu akun admin yang aktif.');
        }

        $admin->update($validated);

        return redirect()
            ->route('admin.admins.index')
            ->with('success', 'Akun admin berhasil diperbarui.');
    }

    /**
     * Remove the given administrator.
     */
    public function destroy(Request $request, User $admin): RedirectResponse
    {
        $this->ensureAdminAccount($admin);

        if ($admin->is($request->user())) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        if ($this->isLastActiveAdmin($admin)) {
            return back()->with('error', 'Minimal harus ada satu akun admin yang aktif.');
        }

        $admin->delete();

        return redirect()
            ->route('admin.admins.index')
            ->with('success', 'Akun admin berhasil dihapus.');
    }

    /**
     * Abort when the given user is not an administrator account.
     */
    private function ensureAdminAccount(User $admin): void
    {
        abort_unless($admin->role === 'admin', 404);
    }

    /**
     * Determine whether the given administrator is the only active one left.
     */
    private function isLastActiveAdmin(User $admin): bool
    {
        if (! $admin->isAdmin()) {
            return false;
        }

        return User::query()
            ->where('role', 'admin')
            ->where('is_active', true)
            ->whereKeyNot($admin->getKey())
            ->doesntExist();
    }
}
