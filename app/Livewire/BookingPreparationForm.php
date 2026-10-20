<?php

namespace App\Livewire;

use App\Models\GuestRegistration;
use App\Models\Room;
use App\Services\BookingPreparation;
use App\Services\BookingSchedule;
use App\Services\BookingService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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

    public array $additionalDates = [];

    public function addDate(): void
    {
        if (count($this->additionalDates) >= 365) {
            $this->addError('additionalDates', 'Maksimal 365 tanggal tambahan per pengajuan.');

            return;
        }
        $this->additionalDates[] = '';
        $this->checked = false;
    }

    public function removeDate(int $index): void
    {
        unset($this->additionalDates[$index]);
        $this->additionalDates = array_values($this->additionalDates);
        $this->checked = false;
    }

    public string $start_time = '';

    public string $end_time = '';

    public string $agenda = '';

    public string $notes = '';

    public string $guest_reference = '';

    public string $duration = '60';

    public bool $outsideWorkHours = false;

    public bool $meeting_preparation = false;

    public $participant_count = null;

    public bool $consumption_snack = false;

    public bool $consumption_meal = false;

    public bool $it_support = false;

    #[Locked]
    public bool $checked = false;

    public function boot(): void
    {
        Gate::authorize('access-employee');
    }

    public function mount(int $roomId): void
    {
        abort_unless(Room::query()->visibleTo(auth()->user())->whereKey($roomId)->exists(), 404);
        $this->roomId = $roomId;
        $this->submissionToken = (string) Str::uuid();
        $this->date = app(BookingPreparation::class)->earliestStart()->toDateString();
    }

    public function updated(string $property): void
    {
        $this->checked = false;
        $this->resetValidation();
        if ($property === 'meeting_preparation' && ! $this->meeting_preparation) {
            $this->participant_count = null;
            $this->consumption_snack = false;
            $this->consumption_meal = false;
        }
        if ($property === 'end_time') {
            $this->duration = 'custom';
        }
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
        $this->prepareTime();
        $room = $this->resolveRoom();
        if (trim($this->guest_reference) !== '') {
            abort_unless(auth()->user()->fresh()->canHandleGuests(), 403);
        }
        $reason = $preparation->unavailableReason($room, auth()->user()->fresh());
        if ($reason) {
            $this->addError('room', $reason);

            return;
        }
        $preparation->validate($this->only(['date', 'start_time', 'end_time', 'agenda', 'notes', 'meeting_preparation', 'participant_count', 'consumption_snack', 'consumption_meal', 'it_support', 'guest_reference']), $room);
        $approvalRoom = clone $room;
        $approvalRoom->requires_approval = trim($this->guest_reference) !== '' || $room->requires_approval || $this->consumption_snack || $this->consumption_meal || $this->it_support;
        app(BookingService::class)->assertPicAvailable($approvalRoom);
        app(BookingService::class)->assertNoConflict($room->id, $this->only(['date', 'start_time', 'end_time']));
        $this->validate(['additionalDates' => ['array', 'max:365'], 'additionalDates.*' => ['required', 'date_format:Y-m-d', 'distinct']]);
        if (in_array($this->date, $this->additionalDates, true)) {
            $this->addError('additionalDates', 'Tanggal booking tidak boleh berulang.');

            return;
        }
        foreach ($this->additionalDates as $date) {
            $data = array_replace($this->only(['date', 'start_time', 'end_time', 'agenda', 'notes', 'meeting_preparation', 'participant_count', 'consumption_snack', 'consumption_meal', 'it_support', 'guest_reference']), ['date' => $date]);
            $preparation->validate($data, $room);
            app(BookingService::class)->assertNoConflict($room->id, $data);
        }
        $this->checked = true;
        $this->dispatch('plan-checked');
    }

    public function submitBooking(BookingService $service): void
    {
        $this->checked = false;
        $this->resetValidation();
        $this->prepareTime();
        $this->validate(['additionalDates' => ['array', 'max:365'], 'additionalDates.*' => ['required', 'date_format:Y-m-d', 'distinct']]);
        $booking = $service->submitMany(auth()->user(), $this->roomId,
            $this->only(['date', 'start_time', 'end_time', 'agenda', 'notes', 'meeting_preparation', 'participant_count', 'consumption_snack', 'consumption_meal', 'it_support', 'guest_reference']), $this->submissionToken, $this->additionalDates);
        session()->flash('booking_series_count', 1 + count($this->additionalDates));
        session()->flash('status', $booking->status === 'approved'
            ? 'Booking berhasil. Ruangan telah dipesan.' : 'Pengajuan berhasil dikirim. Menunggu persetujuan PIC.');
        $this->redirectRoute('my-bookings.show', ['booking' => $booking->id]);
    }

    private function resolveRoom(): Room
    {
        $room = Room::query()->visibleTo(auth()->user())->with(['branch', 'floor', 'facilities', 'allowedOrganizationalUnits'])->find($this->roomId);
        abort_unless($room, 404);

        return $room;
    }

    private function prepareTime(): void
    {
        $this->validate(['duration' => ['required', Rule::in([
            ...array_map('strval', array_keys(config('booking.duration_options'))), 'custom',
        ])]], ['duration.in' => 'Pilih durasi yang tersedia atau atur jam selesai sendiri.']);
        $this->calculateEndTime();
    }

    public function render(): View
    {
        $room = $this->resolveRoom();
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

        $canHandleGuests = auth()->user()->fresh()->canHandleGuests();

        return view('livewire.booking-preparation-form', [
            'canHandleGuests' => $canHandleGuests,
            'guestOptions' => $canHandleGuests
                ? GuestRegistration::where('branch_id', $room->branch_id)->where('expires_at', '>', now())->orderBy('name')->orderBy('id')->get(['reference', 'name', 'organization', 'created_at'])
                : collect(),
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
