<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BranchFilterRequest;
use App\Http\Requests\ResetUserPasswordRequest;
use App\Http\Requests\SaveUserRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Models\Branch;
use App\Models\OrganizationalUnit;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(BranchFilterRequest $request): View
    {
        return view('admin.users.index', [
            'users' => User::with(['organizationalUnit', 'branches'])
                ->when($request->filled('branch_id'), fn ($query) => $query->where(fn ($access) => $access
                    ->where('role', User::ROLE_SUPER_ADMIN)
                    ->orWhereHas('branches', fn ($branches) => $branches->where('branches.id', $request->integer('branch_id')))))
                ->orderBy('name')->orderBy('id')->paginate(20)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', ['units' => OrganizationalUnit::orderBy('name')->get(), 'branches' => Branch::orderBy('name')->get()]);
    }

    public function store(SaveUserRequest $request, UserService $service): RedirectResponse
    {
        $user = new User;
        $user = $service->save($user, $request->validated());

        return redirect()->route('admin.users.edit', $user)->with('status', 'Pengguna berhasil ditambahkan.');
    }

    public function show(User $user): RedirectResponse
    {
        return redirect()->route('admin.users.edit', $user);
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'userRecord' => $user->load('branches'),
            'branches' => Branch::orderBy('name')->get(),
            'units' => OrganizationalUnit::orderBy('name')->get(),
        ]);
    }

    public function update(SaveUserRequest $request, User $user, UserService $service): RedirectResponse
    {
        $user = $service->save($user, $request->validated());

        return $this->redirectAfterChange($request, $user, 'Pengguna berhasil diperbarui.');
    }

    public function status(UpdateStatusRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $user->is_active = $data['is_active'];
        $user->save();

        return $this->redirectAfterChange($request, $user, $user->is_active ? 'Pengguna berhasil diaktifkan.' : 'Pengguna berhasil dinonaktifkan.');
    }

    public function resetPassword(ResetUserPasswordRequest $request, User $user, UserService $service): RedirectResponse
    {
        $service->resetPassword($user, $request->validated('password'));

        return redirect()->route('admin.users.edit', $user)->with('status', 'Password berhasil direset. Gunakan password baru untuk login berikutnya.');
    }

    private function redirectAfterChange(Request $request, User $user, string $message): RedirectResponse
    {
        if ($request->user()->is($user)) {
            if (! $user->is_active) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors(['email' => $message]);
            }
            if ($user->role !== User::ROLE_SUPER_ADMIN) {
                return redirect()->route($user->dashboardRouteName())->with('status', $message);
            }
        }

        return redirect()->route('admin.users.edit', $user)->with('status', $message);
    }
}
