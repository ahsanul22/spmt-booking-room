<?php

namespace App\Http\Controllers\Pic;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    private function query(Request $request): Builder
    {
        return Booking::with(['room', 'applicant', 'organizationalUnit'])
            ->where('requires_approval', true)
            ->when(! $request->user()->can('access-admin'), fn ($query) => $query
                ->whereHas('room.pics', fn ($pics) => $pics->where('users.id', $request->user()->id)));
    }

    public function index(Request $request): View
    {
        $approvals = $this->query($request)->where('status', 'pending')->orderBy('date')->orderBy('start_time')->paginate(15);
        $history = false;
        return view('pic.approvals.index', compact('approvals', 'history'));
    }

    public function history(Request $request): View
    {
        $approvals = $this->query($request)->whereIn('status', ['approved', 'rejected'])->latest('decided_at')->paginate(15);
        $history = true;
        return view('pic.approvals.index', compact('approvals', 'history'));
    }

    public function show(Request $request, int $approval): View
    {
        $booking = $this->query($request)->findOrFail($approval);
        $canDecide = $request->user()->role === User::ROLE_ROOM_PIC && $booking->status === 'pending';
        return view('pic.approvals.show', compact('booking', 'canDecide'));
    }

    public function decide(Request $request, int $approval, BookingService $service): RedirectResponse
    {
        $data = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'rejection_reason' => ['required_if:decision,rejected', 'nullable', 'string', 'max:2000'],
        ]);
        $booking = $this->query($request)->findOrFail($approval);
        $service->decide($request->user(), $booking, $data['decision'], $data['rejection_reason'] ?? null);
        return redirect()->route('pic.approvals.show', $booking->id)->with('status', 'Keputusan pengajuan berhasil disimpan.');
    }
}
