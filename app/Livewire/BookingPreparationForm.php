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

    public function updated(): void
    {
        $this->checked = false;
        $this->resetValidation();
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

        return view('livewire.booking-preparation-form', [
            'room' => $room,
            'scheduleDateValid' => app(BookingSchedule::class)->validDate($this->date),
            'daySlots' => app(BookingSchedule::class)->validDate($this->date) ? app(BookingSchedule::class)->slots($this->date, $this->date, $room->id) : collect(),
            'scheduleUpdatedAt' => now(config('booking.timezone'))->format('H:i:s'),
            'unavailableReason' => $preparation->unavailableReason($room, auth()->user()->fresh()),
            'earliestStart' => $preparation->earliestStart(),
        ]);
    }
}
