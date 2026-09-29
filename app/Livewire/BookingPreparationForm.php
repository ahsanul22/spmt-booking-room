<?php

namespace App\Livewire;

use App\Models\Room;
use App\Services\BookingPreparation;
use App\Services\BookingService;
use App\Services\BookingSchedule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BookingPreparationForm extends Component
{
    #[Locked]
    public int $roomId;

    #[Locked]
    public string $submissionToken;

    public string $date = '';
    public string $start_time = '';
    public string $end_time = '';
    public string $agenda = '';
    public string $notes = '';
    public string $duration = '60';
    public bool $outsideWorkHours = false;

    #[Locked]
    public bool $checked = false;

    public function boot(): void
    {
        Gate::authorize('access-employee');
    }

    public function mount(int $roomId): void
    {
        $this->roomId = $roomId;
        $this->submissionToken = (string) Str::uuid();
        $this->date = app(BookingPreparation::class)->earliestStart()->toDateString();
    }

    public function updated(string $property): void
    {
        $this->checked = false;
        $this->resetValidation();
        if (in_array($property, ['start_time', 'duration'], true)) {
            $this->calculateEndTime();
        }
    }

    private function calculateEndTime(): void
    {
        if ($this->duration === 'custom') {
            return;
        }
        $durations = config('booking.duration_options');
        if (! isset($durations[$this->duration]) || ! preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $this->start_time)) {
            $this->end_time = '';
            return;
        }
        [$hour, $minute] = array_map('intval', explode(':', $this->start_time));
        $end = $hour * 60 + $minute + (int) $this->duration;
        $this->end_time = $end < 1440 ? sprintf('%02d:%02d', intdiv($end, 60), $end % 60) : '';
    }

    public function checkPlan(BookingPreparation $preparation): void
    {
        $this->checked = false;
        $this->resetValidation();
        $room = Room::with(['floor', 'facilities', 'allowedOrganizationalUnits'])->findOrFail($this->roomId);
        $reason = $preparation->unavailableReason($room, auth()->user()->fresh());
        if ($reason) {
            $this->addError('room', $reason);
            return;
        }
        $preparation->validate($this->only(['date', 'start_time', 'end_time', 'agenda', 'notes']));
        app(BookingService::class)->assertNoConflict($room->id, $this->only(['date', 'start_time', 'end_time']));
        $this->checked = true;
        $this->dispatch('plan-checked');
    }

    public function submitBooking(BookingService $service): void
    {
        $this->checked = false;
        $this->resetValidation();
        $booking = $service->submit(auth()->user(), $this->roomId,
            $this->only(['date', 'start_time', 'end_time', 'agenda', 'notes']), $this->submissionToken);
        session()->flash('status', $booking->status === 'approved'
            ? 'Booking berhasil. Ruangan telah dipesan.' : 'Pengajuan berhasil dikirim. Menunggu persetujuan PIC.');
        $this->redirectRoute('my-bookings.show', ['booking' => $booking->id]);
    }

    public function render(): View
    {
        $room = Room::with(['floor', 'facilities', 'allowedOrganizationalUnits'])->findOrFail($this->roomId);
        $preparation = app(BookingPreparation::class);
        $startOptions = [];
        for ($minute = 0; $minute < 1440; $minute += config('booking.time_step_minutes')) {
            $time = sprintf('%02d:%02d', intdiv($minute, 60), $minute % 60);
            if ($this->outsideWorkHours || ($time >= config('booking.workday_start') && $time < config('booking.workday_end'))) {
                $startOptions[] = $time;
            }
        }
        // Keep the selected value visible when the user switches the picker range.
        if ($this->start_time !== '' && ! in_array($this->start_time, $startOptions, true)) {
            $startOptions[] = $this->start_time;
            sort($startOptions);
        }

        return view('livewire.booking-preparation-form', [
            'startOptions' => $startOptions,
            'outsideWorkday' => $this->start_time !== '' && ($this->start_time < config('booking.workday_start') || $this->start_time >= config('booking.workday_end') || $this->end_time > config('booking.workday_end')),
            'room' => $room,
            'scheduleDateValid' => app(BookingSchedule::class)->validDate($this->date),
            'daySlots' => app(BookingSchedule::class)->validDate($this->date) ? app(BookingSchedule::class)->slots($this->date, $this->date, $room->id) : collect(),
            'scheduleUpdatedAt' => now(config('booking.timezone'))->format('H:i:s'),
            'unavailableReason' => $preparation->unavailableReason($room, auth()->user()->fresh()),
            'earliestStart' => $preparation->earliestStart(),
        ]);
    }
}
