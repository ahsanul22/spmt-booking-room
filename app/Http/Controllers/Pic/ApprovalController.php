<?php

namespace App\Http\Controllers\Pic;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    private const PAGE_SIZE = 15;

    private function query(Request $request): Builder
    {
        return Booking::with(['room', 'applicant', 'organizationalUnit'])
            ->where('requires_approval', true)
            ->when(! $request->user()->can('access-admin'), fn ($query) => $query
                ->whereHas('room.pics', fn ($pics) => $pics->where('users.id', $request->user()->id)));
    }

    public function index(Request $request): View
    {
        $approvals = $this->query($request)->where('status', 'pending')->orderBy('date')->orderBy('start_time')->paginate(self::PAGE_SIZE);
        $history = false;

        return view('pic.approvals.index', compact('approvals', 'history') + ['approvalRoutePrefix' => $this->routePrefix($request)]);
    }

    public function history(Request $request): View
    {
        $approvals = $this->query($request)->whereIn('status', ['approved', 'rejected'])->latest('decided_at')->paginate(self::PAGE_SIZE);
        $history = true;

        return view('pic.approvals.index', compact('approvals', 'history') + ['approvalRoutePrefix' => $this->routePrefix($request)]);
    }

    public function show(Request $request, int $approval): View
    {
        $booking = $this->query($request)->findOrFail($approval);
        $canDecide = $booking->status === 'pending';

        return view('pic.approvals.show', compact('booking', 'canDecide') + ['approvalRoutePrefix' => $this->routePrefix($request)]);
    }

    public function decide(Request $request, int $approval, BookingService $service): RedirectResponse
    {
        // Keep compatibility with the existing detail form and API field.
        if ($request->has('decision_notes')) {
            $request->merge(['rejection_reason' => $request->input('decision_notes')]);
        }
        $data = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'decision_notes' => ['nullable', 'string', 'max:2000'],
            'return_to' => ['nullable', 'in:list,detail'],
            'page' => ['nullable', 'integer', 'min:1'],
            'rejection_reason' => ['required_if:decision,rejected', 'nullable', 'string', 'max:2000'],
        ], [
            'rejection_reason.required_if' => 'Alasan penolakan wajib diisi.',
            'rejection_reason.max' => 'Alasan penolakan maksimal 2.000 karakter.',
        ]);
        $booking = $this->query($request)->findOrFail($approval);
        $service->decide($request->user(), $booking, $data['decision'], $data['rejection_reason'] ?? null);
        $prefix = $this->routePrefix($request);
        $destination = route($prefix.'.show', $booking->id);
        if (($data['return_to'] ?? 'detail') === 'list') {
            $remaining = $this->query($request)->where('status', 'pending')->count();
            $lastPage = max(1, (int) ceil($remaining / self::PAGE_SIZE));
            $destination = route($prefix.'.index', ['page' => min($data['page'] ?? 1, $lastPage)]);
        }

        return redirect()->to($destination)->with('status', 'Keputusan pengajuan berhasil disimpan.');
    }

    private function routePrefix(Request $request): string
    {
        return $request->routeIs('admin.approvals.*') ? 'admin.approvals' : 'pic.approvals';
    }
}
