<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveOrganizationalUnitRequest;
use App\Models\OrganizationalUnit;
use App\Services\OrganizationalUnitService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizationalUnitController extends Controller
{
    public function index(): View
    {
        return view('admin.organizational-units.index', [
            'units' => OrganizationalUnit::with('parent')->orderBy('name')->orderBy('id')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.organizational-units.create', ['units' => $this->parentOptions()]);
    }

    public function store(SaveOrganizationalUnitRequest $request, OrganizationalUnitService $service): RedirectResponse
    {
        $unit = $service->save($request->validated());

        return redirect()->route('admin.organizational-units.show', $unit)->with('status', 'Unit organisasi berhasil ditambahkan.');
    }

    public function show(OrganizationalUnit $unit): View
    {
        return view('admin.organizational-units.show', ['unit' => $unit->load(['parent', 'children' => fn ($query) => $query->orderBy('name')])]);
    }

    public function edit(OrganizationalUnit $unit): View
    {
        return view('admin.organizational-units.edit', ['unit' => $unit, 'units' => $this->parentOptions($unit)]);
    }

    public function update(SaveOrganizationalUnitRequest $request, OrganizationalUnit $unit, OrganizationalUnitService $service): RedirectResponse
    {
        $service->save($request->validated(), $unit);

        return redirect()->route('admin.organizational-units.show', $unit)->with('status', 'Unit organisasi berhasil diperbarui.');
    }

    public function status(Request $request, OrganizationalUnit $unit): RedirectResponse
    {
        $data = $request->validate(['is_active' => ['required', 'boolean']]);
        $unit->update($data);

        return redirect()->route('admin.organizational-units.show', $unit)
            ->with('status', $unit->is_active ? 'Unit organisasi berhasil diaktifkan.' : 'Unit organisasi berhasil dinonaktifkan.');
    }

    private function parentOptions(?OrganizationalUnit $unit = null): Collection
    {
        return OrganizationalUnit::whereIn('type', ['directorate', 'division'])
            ->when($unit, fn ($query) => $query->where('id', '!=', $unit->id))
            ->orderBy('name')->get();
    }
}
