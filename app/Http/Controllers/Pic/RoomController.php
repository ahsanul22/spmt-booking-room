<?php

namespace App\Http\Controllers\Pic;

use App\Http\Controllers\Controller;
use App\Http\Requests\BranchFilterRequest;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function index(BranchFilterRequest $request): View
    {
        $branches = collect();
        $rooms = $request->user()->managedRooms()
            ->visibleTo($request->user())
            ->with(['branch', 'floor', 'facilities'])
            ->when($request->filled('branch_id'), fn ($query) => $query->where('branch_id', $request->integer('branch_id')))
            ->orderBy('rooms.name')->orderBy('rooms.id')->paginate(9)->withQueryString();

        return view('pic.rooms.index', compact('rooms', 'branches'));
    }
}
