<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Services\MonthlyBookingReport;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ListingFilters extends Component
{
    #[Locked]
    public string $destination;

    #[Locked]
    public string $kind = 'branch';

    public string $branch = '';

    public string $room = '';

    public string $month = '';

    public string $event = '';

    public function boot(): void
    {
        Gate::authorize('access-general');
    }

    public function mount(string $destination, string $kind = 'branch'): void
    {
        $this->destination = $destination;
        $this->kind = $kind;
        $this->authorizeDestination();
        $this->branch = $kind !== 'whatsapp' ? (string) request('branch_id', '') : '';
        $this->room = $kind === 'report' ? (string) request('room_id', '') : '';
        $this->month = $kind === 'report' ? (string) request('month', now(config('booking.timezone'))->format('Y-m')) : '';
        $this->event = $kind === 'whatsapp' ? (string) request('event', '') : '';
    }

    private function authorizeDestination(): void
    {
        $allowed = match ($this->kind) {
            'branch' => ['admin.rooms.index', 'admin.bookings.index', 'admin.approvals.index', 'admin.approvals.history', 'admin.users.index', 'pic.rooms.index', 'pic.approvals.index', 'pic.approvals.history'],
            'report' => ['admin.reports.index', 'pic.reports.index'],
            'whatsapp' => ['admin.whatsapp.index'],
            default => [],
        };
        abort_unless(in_array($this->destination, $allowed, true), 403);
        Gate::authorize($this->kind !== 'report' || str_starts_with($this->destination, 'admin.') ? 'access-admin' : 'access-pic');
    }

    public function updatedBranch(): void
    {
        $this->room = '';
    }

    public function applyFilters(): void
    {
        $this->authorizeDestination();
        $this->validate([
            'branch' => ['bail', Rule::prohibitedIf(! auth()->user()->can('access-admin')), 'nullable', 'integer', 'min:1', 'exists:branches,id'],
            'room' => ['nullable', 'integer', 'min:1'],
            'month' => [Rule::requiredIf($this->kind === 'report'), 'nullable', 'date_format:Y-m', 'after_or_equal:1000-01', 'before_or_equal:9998-12'],
            'event' => ['nullable', Rule::in(['approval_request', 'consumption'])],
        ]);
        if ($this->kind === 'report' && $this->room !== '') {
            abort_unless(app(MonthlyBookingReport::class)->rooms(auth()->user())
                ->when($this->branch !== '', fn ($query) => $query->where('branch_id', $this->branch))
                ->whereKey($this->room)->exists(), 404);
        }
        $filters = match ($this->kind) {
            'report' => ['branch_id' => $this->branch, 'room_id' => $this->room, 'month' => $this->month],
            'whatsapp' => ['event' => $this->event],
            default => ['branch_id' => $this->branch],
        };
        // Livewire navigation keeps the URL, export links and existing GET validation in sync.
        $this->redirectRoute($this->destination, array_filter($filters, fn ($value) => $value !== ''), navigate: true);
    }

    public function render(): View
    {
        $this->authorizeDestination();
        // Draft filters render before applyFilters validates them. Never send an
        // arbitrary draft string to a bigint column while populating room options.
        $validBranch = $this->branch === '' || filter_var($this->branch, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false;

        return view('livewire.listing-filters', [
            'branches' => auth()->user()->can('access-admin') ? Branch::orderBy('name')->get() : collect(),
            'rooms' => $this->kind === 'report' && $validBranch ? app(MonthlyBookingReport::class)->rooms(auth()->user())
                ->when($this->branch !== '', fn ($query) => $query->where('branch_id', $this->branch))
                ->with('branch')->orderBy('name')->get() : collect(),
        ]);
    }
}
