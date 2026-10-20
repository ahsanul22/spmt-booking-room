<?php

namespace App\Livewire;

use App\Models\Branch;
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

    #[Locked]
    public ?int $branchId = null;

    public string $draftBranch = '';

    public function applyBranch(): void
    {
        $this->selectBranch($this->draftBranch);
    }

    public function selectBranch(string $id): void
    {
        Gate::authorize('access-admin');
        abort_unless($id === '' || (filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false && Branch::whereKey($id)->exists()), 422);
        $this->branchId = $id === '' ? null : (int) $id;
        $this->draftBranch = $id;
        $this->resetPage(pageName: 'roomsPage');
    }

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
        $rooms = Room::query()->visibleTo(auth()->user())->when($this->branchId, fn ($query) => $query->where('branch_id', $this->branchId))
            ->where('is_active', true)
            ->with(['branch', 'floor', 'facilities', 'allowedOrganizationalUnits'])
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
            'branches' => auth()->user()->can('access-admin') ? Branch::orderBy('name')->get() : collect(),
            'rooms' => $rooms,
            'filters' => ['all' => 'Semua Ruangan', 'featured' => $featuredName, 'other' => 'Ruang Rapat Lainnya'],
        ]);
    }
}
