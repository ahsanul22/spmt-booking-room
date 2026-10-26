<?php

namespace App\Http\Controllers\Pic;

use App\Http\Controllers\Controller;
use App\Http\Requests\BranchFilterRequest;
use App\Http\Requests\DecideBookingRequest;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Room;
use App\Services\BookingService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    private const PAGE_SIZE = 15;

    private function query(BranchFilterRequest $request): Builder
    {
        return Booking::with(['room.branch', 'applicant', 'organizationalUnit'])
            ->whereIn('room_id', Room::query()->visibleTo($request->user())->select('rooms.id'))
            ->where('requires_approval', true)
            ->when($request->filled('branch_id'), fn ($query) => $query->whereHas('room', fn ($rooms) => $rooms->where('branch_id', $request->integer('branch_id'))))
            ->when(! $request->user()->can('access-admin'), fn ($query) => $query
                ->whereHas('room.pics', fn ($pics) => $pics->where('users.id', $request->user()->id)));
    }

    public function index(BranchFilterRequest $request): View
    {
        $approvals = $this->query($request)->where('status', 'pending')->orderBy('date')->orderBy('start_time')->paginate(self::PAGE_SIZE)->withQueryString();
        $history = false;

        return view('pic.approvals.index', compact('approvals', 'history') + ['branches' => ($request->user()->can('access-admin') ? Branch::orderBy('name')->get() : collect()), 'approvalRoutePrefix' => $this->routePrefix($request)]);
    }

    public function history(BranchFilterRequest $request): View
    {
        $approvals = $this->query($request)->whereIn('status', ['approved', 'rejected'])->latest('decided_at')->paginate(self::PAGE_SIZE)->withQueryString();
        $history = true;

        return view('pic.approvals.index', compact('approvals', 'history') + ['branches' => ($request->user()->can('access-admin') ? Branch::orderBy('name')->get() : collect()), 'approvalRoutePrefix' => $this->routePrefix($request)]);
    }

    public function show(BranchFilterRequest $request, int $approval): View
    {
        $booking = $this->query($request)->findOrFail($approval);
        $canDecide = $booking->status === 'pending';

        return view('pic.approvals.show', compact('booking', 'canDecide') + ['branches' => ($request->user()->can('access-admin') ? Branch::orderBy('name')->get() : collect()), 'approvalRoutePrefix' => $this->routePrefix($request)]);
    }

    public function decide(DecideBookingRequest $request, int $approval, BookingService $service): RedirectResponse
    {
        $data = $request->validated();
        $booking = $this->query($request)->findOrFail($approval);
        $service->decide($request->user(), $booking, $data['decision'], $data['rejection_reason'] ?? null, array_intersect_key($data, array_flip(['approved_consumption_snack', 'approved_consumption_meal', 'approved_it_support'])));
        $prefix = $this->routePrefix($request);
        $destination = route($prefix.'.show', $booking->id);
        if (($data['return_to'] ?? 'detail') === 'list') {
            $remaining = $this->query($request)->where('status', 'pending')->count();
            $lastPage = max(1, (int) ceil($remaining / self::PAGE_SIZE));
            $destination = route($prefix.'.index', array_filter(['page' => min($data['page'] ?? 1, $lastPage), 'branch_id' => $data['branch_id'] ?? null], fn ($value) => $value !== null && $value !== ''));
        }

        return redirect()->to($destination)->with('status', 'Keputusan pengajuan berhasil disimpan.');
    }

    private function routePrefix(Request $request): string
    {
        return $request->routeIs('admin.approvals.*') ? 'admin.approvals' : 'pic.approvals';
    }
}
