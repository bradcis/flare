<?php

namespace App\Flare\View\Livewire\Admin\Affixes;

use Livewire\Component;
use Livewire\WithPagination;
use App\Flare\Models\ItemAffix;
use App\Flare\View\Livewire\Core\DataTables\WithSorting;

class DataTable extends Component
{
    use WithPagination, WithSorting;

    public $search      = '';
    public $sortField   = 'name';
    public $perPage     = 10;

    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        return view('components.livewire.admin.affixes.data-table', [
            'itemAffixes' => ItemAffix::dataTableSearch($this->search)
                                      ->orderBy($this->sortField, $this->sortBy)
                                      ->paginate($this->perPage),
        ]);
    }
}
