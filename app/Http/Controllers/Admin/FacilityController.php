<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaginationRequest;
use App\Http\Requests\SaveFacilityRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Models\Facility;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FacilityController extends Controller
{
    public function index(PaginationRequest $request): View
    {
        return view('admin.facilities.index', ['facilities' => Facility::orderBy('name')->orderBy('id')->paginate(20)]);
    }

    public function create(): View
    {
        return view('admin.facilities.create');
    }

    public function store(SaveFacilityRequest $request): RedirectResponse
    {
        $facility = Facility::create($request->validated());

        return redirect()->route('admin.facilities.show', $facility)->with('status', 'Fasilitas berhasil ditambahkan.');
    }

    public function show(Facility $facility): View
    {
        return view('admin.facilities.show', ['facility' => $facility]);
    }

    public function edit(Facility $facility): View
    {
        return view('admin.facilities.edit', ['facility' => $facility]);
    }

    public function update(SaveFacilityRequest $request, Facility $facility): RedirectResponse
    {
        $facility->update($request->validated());

        return redirect()->route('admin.facilities.show', $facility)->with('status', 'Fasilitas berhasil diperbarui.');
    }

    public function status(UpdateStatusRequest $request, Facility $facility): RedirectResponse
    {
        $data = $request->validated();
        $facility->update($data);

        return redirect()->route('admin.facilities.show', $facility)
            ->with('status', $facility->is_active ? 'Fasilitas berhasil diaktifkan.' : 'Fasilitas berhasil dinonaktifkan.');
    }
}
