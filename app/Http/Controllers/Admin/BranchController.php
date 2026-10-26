<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaginationRequest;
use App\Http\Requests\SaveBranchRequest;
use App\Models\Branch;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function index(PaginationRequest $request): View
    {
        return view('admin.branches.index', ['branches' => Branch::withCount('rooms')->orderBy('name')->paginate(20)]);
    }

    public function store(SaveBranchRequest $request): RedirectResponse
    {
        Branch::create($request->validated());

        return redirect()->route('admin.branches.index')->with('status', 'Lokasi berhasil ditambahkan. Silakan tambahkan ruangan dan tetapkan PIC.');
    }

    public function update(SaveBranchRequest $request, Branch $branch): RedirectResponse
    {
        $branch->update($request->validated());

        return redirect()->route('admin.branches.index')->with('status', 'Nama lokasi berhasil diperbarui.');
    }
}
