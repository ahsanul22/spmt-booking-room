<?php

namespace App\Http\Controllers\Pic;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function index(Request $request): View
    {
        abort_if(! $request->user()->can('access-admin') && $request->filled('branch_id'), 403);
        $request->validate(['branch_id' => ['nullable', 'integer', 'exists:branches,id']]);
        $branches = collect();
        $rooms = $request->user()->managedRooms()
            ->visibleTo($request->user())
            ->with(['branch', 'floor', 'facilities'])
            ->when($request->filled('branch_id'), fn ($query) => $query->where('branch_id', $request->integer('branch_id')))
            ->orderBy('rooms.name')->orderBy('rooms.id')->paginate(9)->withQueryString();

        return view('pic.rooms.index', compact('rooms', 'branches'));
    }
}
