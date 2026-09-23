<?php

namespace App\Livewire;

use App\Models\Room;
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

    public function boot(): void
    {
        Gate::authorize('access-employee');
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
            ->with(['floor', 'facilities'])
            ->when($this->filter === 'featured', fn ($query) => $query
                ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower(trim($featuredName))]))
            ->when($this->filter === 'other', fn ($query) => $query
                ->whereRaw('LOWER(TRIM(name)) <> ?', [mb_strtolower(trim($featuredName))]))
            ->orderBy('name')->orderBy('id')
            ->paginate(9, pageName: 'roomsPage');

        return view('livewire.room-catalog', [
            'rooms' => $rooms,
            'filters' => ['all' => 'Semua Ruangan', 'featured' => $featuredName, 'other' => 'Ruang Rapat Lainnya'],
        ]);
    }
}
