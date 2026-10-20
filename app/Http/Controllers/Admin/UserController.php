<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveUserRequest;
use App\Models\Branch;
use App\Models\OrganizationalUnit;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate(['branch_id' => ['nullable', 'integer', 'exists:branches,id']]);

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

    public function store(SaveUserRequest $request): RedirectResponse
    {
        $user = new User;
        $this->save($user, $request->validated());

        return redirect()->route('admin.users.edit', $user)->with('status', 'User berhasil ditambahkan.');
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

    public function update(SaveUserRequest $request, User $user): RedirectResponse
    {
        $this->save($user, $request->validated());

        return $this->redirectAfterChange($request, $user, 'User berhasil diperbarui.');
    }

    public function status(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate(['is_active' => ['required', 'boolean']]);
        $user->is_active = $data['is_active'];
        $user->save();

        return $this->redirectAfterChange($request, $user, $user->is_active ? 'User berhasil diaktifkan.' : 'User berhasil dinonaktifkan.');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate(['password' => ['required', 'string', 'confirmed', Password::min(8)]]);
        $this->setPassword($user, $data['password']);
        $user->save();

        return redirect()->route('admin.users.edit', $user)->with('status', 'Password berhasil direset. Gunakan password baru untuk login berikutnya.');
    }

    private function save(User $user, array $data): void
    {
        try {
            DB::transaction(function () use ($user, $data) {
                if ($user->exists) {
                    User::whereKey($user->id)->lockForUpdate()->firstOrFail();
                }
                $user->name = $data['name'];
                $user->email = $data['email'];
                // Privileged values and location permissions are only written after Admin authorization.
                $user->role = $data['role'];
                $user->organizational_unit_id = $data['organizational_unit_id'] ?? null;
                $user->is_active = $data['is_active'];
                $user->is_receptionist = in_array($data['role'], [User::ROLE_USER, User::ROLE_ROOM_PIC], true)
                    && (bool) ($data['is_receptionist'] ?? $user->is_receptionist);
                if (array_key_exists('whatsapp_number', $data)) {
                    $user->whatsapp_number = $data['whatsapp_number'];
                }
                if (! empty($data['password'])) {
                    $this->setPassword($user, $data['password']);
                }
                $user->save();
                if (array_key_exists('branch_ids', $data)) {
                    $user->branches()->sync($data['branch_ids']);
                }
            });
        } catch (QueryException $error) {
            if ((string) $error->getCode() === '23505') {
                throw ValidationException::withMessages(['email' => 'Email sudah digunakan oleh akun lain.']);
            }
            throw $error;
        }
    }

    private function setPassword(User $user, string $password): void
    {
        $user->password = Hash::make($password);
        $user->setRememberToken(Str::random(60));
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
