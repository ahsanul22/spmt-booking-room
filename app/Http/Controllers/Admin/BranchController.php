<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    public function index()
    {
        return view('admin.branches.index', ['branches' => Branch::withCount('rooms')->orderBy('name')->paginate(20)]);
    }

    public function store(Request $request)
    {
        Branch::create($request->validate(['name' => ['required', 'string', 'max:255', 'unique:branches,name']]));

        return redirect()->route('admin.branches.index')->with('status', 'Lokasi berhasil ditambahkan. Silakan tambahkan ruangan dan tetapkan PIC.');
    }

    public function update(Request $request, Branch $branch)
    {
        $branch->update($request->validate(['name' => ['required', 'string', 'max:255', Rule::unique('branches')->ignore($branch)]]));

        return redirect()->route('admin.branches.index')->with('status', 'Nama lokasi berhasil diperbarui.');
    }
}
