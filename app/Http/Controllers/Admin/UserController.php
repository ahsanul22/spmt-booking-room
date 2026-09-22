<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveUserRequest;
use App\Models\OrganizationalUnit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::with('organizationalUnit')->orderBy('name')->orderBy('id')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', ['units' => OrganizationalUnit::orderBy('name')->get()]);
    }

    public function store(SaveUserRequest $request): RedirectResponse
    {
        $user = new User;
        $this->save($user, $request->validated());

        return redirect()->route('admin.users.show', $user)->with('status', 'User berhasil ditambahkan.');
    }

    public function show(User $user): View
    {
        return view('admin.users.show', ['userRecord' => $user->load('organizationalUnit')]);
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'userRecord' => $user,
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

        return redirect()->route('admin.users.show', $user)->with('status', 'Password berhasil direset. Gunakan password baru untuk login berikutnya.');
    }

    private function save(User $user, array $data): void
    {
        $user->name = $data['name'];
        $user->email = $data['email'];
        // Privileged fields remain guarded on the model and are assigned explicitly here.
        $user->role = $data['role'];
        $user->organizational_unit_id = $data['organizational_unit_id'] ?? null;
        $user->is_active = $data['is_active'];
        if (! empty($data['password'])) {
            $this->setPassword($user, $data['password']);
        }
        $user->save();
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

        return redirect()->route('admin.users.show', $user)->with('status', $message);
    }
}
