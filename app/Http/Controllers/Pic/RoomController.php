<?php

namespace App\Http\Controllers\Pic;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function index(Request $request): View
    {
        $rooms = $request->user()->managedRooms()
            ->with(['floor', 'facilities'])
            ->orderBy('rooms.name')->orderBy('rooms.id')->paginate(9);

        return view('pic.rooms.index', compact('rooms'));
    }
}
