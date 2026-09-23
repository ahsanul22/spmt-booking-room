<section aria-labelledby="account-title" class="rounded-2xl border border-slate-200/80 bg-surface p-6 shadow-sm">
    <h2 id="account-title" class="font-bold text-primaryDark">Akun Anda</h2>
    <dl class="mt-4 space-y-3 text-sm">
        <div><dt class="text-xs text-slate-500">Login sebagai</dt><dd class="mt-1 break-words font-semibold">{{ $user->name }}</dd></div>
        <div><dt class="text-xs text-slate-500">Role</dt><dd class="mt-1">{{ $user->role }}</dd></div>
        <div><dt class="text-xs text-slate-500">Unit Kerja</dt><dd class="mt-1 break-words">{{ $user->organizationalUnit?->name ?? 'Belum ditentukan' }}</dd></div>
    </dl>
</section>
