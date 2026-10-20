<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\Room;
use App\Services\BookingSchedule;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class RoomSchedule extends Component
{
    #[Locked]
    public string $selectedDate;

    #[Locked]
    public string $month;

    #[Locked]
    public ?int $roomId = null;

    #[Locked]
    public ?int $branchId = null;

    public string $draftBranch = '';

    public string $draftRoom = '';

    public function applyBranch(): void
    {
        $this->selectBranch($this->draftBranch);
    }

    public function applyRoom(): void
    {
        $this->selectRoom($this->draftRoom);
    }

    public function selectBranch(string $id): void
    {
        Gate::authorize('access-admin');
        abort_unless($id === '' || (filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false && Branch::whereKey($id)->exists()), 422);
        $this->branchId = $id === '' ? null : (int) $id;
        $this->draftBranch = $id;
        $this->roomId = null;
        $this->draftRoom = '';
    }

    public function boot(): void
    {
        Gate::authorize('access-general');
    }

    protected function publicOnly(): bool
    {
        return false;
    }

    protected function roomsQuery(): Builder
    {
        return Room::query()->visibleTo(auth()->user())->when($this->branchId, fn ($query) => $query->where('branch_id', $this->branchId))->when($this->publicOnly(), fn ($query) => $query->where('is_active', true)->where('access_type', 'all'));
    }

    public function mount(): void
    {
        $branch = request()->query('branch_id');
        if (auth()->user()->can('access-admin') && is_string($branch) && filter_var($branch, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false && Branch::whereKey($branch)->exists()) {
            $this->branchId = (int) $branch;
        }
        $date = request()->query('date');
        $this->selectedDate = app(BookingSchedule::class)->validDate($date) ? $date : CarbonImmutable::now(config('booking.timezone'))->toDateString();
        $this->month = substr($this->selectedDate, 0, 7).'-01';
        $id = request()->query('room_id');
        if (is_string($id) && filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false) {
            $this->roomId = $this->roomsQuery()->whereKey($id)->value('id');
        }
        $this->draftBranch = (string) $this->branchId;
        $this->draftRoom = (string) $this->roomId;
    }

    public function selectDate(string $date): void
    {
        if (! app(BookingSchedule::class)->validDate($date)) {
            $this->addError('date', 'Pilih tanggal yang valid.');

            return;
        }
        $this->resetValidation();
        $this->selectedDate = $date;
        $this->month = substr($date, 0, 7).'-01';
    }

    public function selectRoom(string $id): void
    {
        abort_unless($id === '' || (filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false && $this->roomsQuery()->whereKey($id)->exists()), 422);
        $this->roomId = $id === '' ? null : (int) $id;
        $this->draftRoom = $id;
    }

    public function changeMonth(int $direction): void
    {
        abort_unless(in_array($direction, [-1, 1], true), 422);
        $date = CarbonImmutable::parse($this->month, config('booking.timezone'))->addMonths($direction)->toDateString();
        if (app(BookingSchedule::class)->validDate($date)) {
            $this->selectDate($date);
        }
    }

    public function today(): void
    {
        $this->selectDate(CarbonImmutable::now(config('booking.timezone'))->toDateString());
    }

    public function render(): View
    {
        $month = CarbonImmutable::parse($this->month, config('booking.timezone'));
        $start = $month->startOfWeek();
        $now = CarbonImmutable::now(config('booking.timezone'));
        $slots = app(BookingSchedule::class)->slots($start->toDateString(), $start->addDays(41)->toDateString(), $this->roomId, $this->publicOnly(), $this->branchId);
        $days = collect(range(0, 41))->map(function ($offset) use ($start, $month, $slots, $now) {
            $date = $start->addDays($offset);
            $items = $slots->where('date', $date->toDateString());

            return ['date' => $date->toDateString(), 'number' => $date->day, 'in_month' => $date->month === $month->month,
                'today' => $date->isSameDay($now), 'counts' => $items->countBy('state')->all(), 'total' => $items->count()];
        });

        return view('livewire.room-schedule', [
            'days' => $days, 'monthLabel' => $month->locale('id')->translatedFormat('F Y'),
            'dayLabel' => CarbonImmutable::parse($this->selectedDate)->locale('id')->translatedFormat('l, d F Y'),
            'daySlots' => $slots->where('date', $this->selectedDate)->values(),
            'branches' => auth()->user()->can('access-admin') ? Branch::orderBy('name')->get() : collect(),
            'rooms' => $this->roomsQuery()->with('branch')->orderBy('name')->get(['id', 'name', 'is_active', 'status', 'branch_id']),
            'updatedAt' => $now->format('H:i:s'),
        ]);
    }
}
