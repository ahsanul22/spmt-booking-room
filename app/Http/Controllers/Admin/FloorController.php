<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaginationRequest;
use App\Http\Requests\SaveFloorRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Models\Floor;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FloorController extends Controller
{
    public function index(PaginationRequest $request): View
    {
        return view('admin.floors.index', ['floors' => Floor::orderBy('floor_number')->orderBy('id')->paginate(20)]);
    }

    public function create(): View
    {
        return view('admin.floors.create');
    }

    public function store(SaveFloorRequest $request): RedirectResponse
    {
        $floor = Floor::create($request->validated());

        return redirect()->route('admin.floors.show', $floor)->with('status', 'Lantai berhasil ditambahkan.');
    }

    public function show(Floor $floor): View
    {
        return view('admin.floors.show', ['floor' => $floor]);
    }

    public function edit(Floor $floor): View
    {
        return view('admin.floors.edit', ['floor' => $floor]);
    }

    public function update(SaveFloorRequest $request, Floor $floor): RedirectResponse
    {
        $floor->update($request->validated());

        return redirect()->route('admin.floors.show', $floor)->with('status', 'Lantai berhasil diperbarui.');
    }

    public function status(UpdateStatusRequest $request, Floor $floor): RedirectResponse
    {
        $data = $request->validated();
        $floor->update($data);

        return redirect()->route('admin.floors.show', $floor)
            ->with('status', $floor->is_active ? 'Lantai berhasil diaktifkan.' : 'Lantai berhasil dinonaktifkan.');
    }
}
