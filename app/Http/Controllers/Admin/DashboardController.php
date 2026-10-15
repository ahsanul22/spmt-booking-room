<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminDashboard;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.dashboard', app(AdminDashboard::class)->data() + [
            'user' => $request->user()->load('organizationalUnit'),
            'title' => 'Dashboard Admin',
        ]);
    }
}
