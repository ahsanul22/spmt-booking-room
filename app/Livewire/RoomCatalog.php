<?php

namespace App\Livewire;

use App\Models\Room;
use App\Services\BookingPreparation;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class RoomCatalog extends Component
{
    use WithPagination;

    #[Locked]
    public string $filter = 'all';

    #[Locked]
    public bool $summary = false;

    public function boot(): void
    {
        Gate::authorize('access-general');
    }

    public function selectFilter(string $filter): void
    {
        abort_unless(in_array($filter, ['all', 'featured', 'other'], true), 422);

        $this->filter = $filter;
        $this->resetPage(pageName: 'roomsPage');
    }

    public function render(): View
    {
        $featuredName = config('room-catalog.featured_room_name');
        $rooms = Room::query()
            ->where('is_active', true)
            ->with(['floor', 'facilities', 'allowedOrganizationalUnits'])
            ->when($this->filter === 'featured', fn ($query) => $query
                ->where(fn ($names) => $names->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower(trim($featuredName))])->orWhereIn('code', config('room-catalog.featured_room_codes', []))))
            ->when($this->filter === 'other', fn ($query) => $query
                ->whereRaw('LOWER(TRIM(name)) <> ?', [mb_strtolower(trim($featuredName))])->where(fn ($codes) => $codes->whereNull('code')->orWhereNotIn('code', config('room-catalog.featured_room_codes', []))))
            ->orderBy('name')->orderBy('id')
            ->paginate($this->summary ? 3 : 9, pageName: 'roomsPage');

        $user = auth()->user()->fresh();
        $reasons = $rooms->getCollection()->mapWithKeys(fn ($room) => [
            $room->id => app(BookingPreparation::class)->unavailableReason($room, $user),
        ]);

        return view('livewire.room-catalog', [
            'reasons' => $reasons,
            'rooms' => $rooms,
            'filters' => ['all' => 'Semua Ruangan', 'featured' => $featuredName, 'other' => 'Ruang Rapat Lainnya'],
        ]);
    }
}
