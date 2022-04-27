<?php

namespace App\Flare\View\Livewire\Admin\Affixes;

use App\Flare\Models\ItemAffix;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

class AffixesTable extends DataTableComponent {

    public function configure(): void {
        $this->setPrimaryKey('id');
    }

    public function builder(): Builder {
        return ItemAffix::where('randomly_generated', false);
    }

    public function columns(): array {
        return [
            Column::make('Name')->format(function ($value, $row) {
                $affixId = ItemAffix::where('name', $value)->first()->id;

                if (auth()->user()->hasRole('Admin')) {
                    return '<a href="/admin/affixes/'. $affixId.'">'.$row->name . '</a>';
                }

                return '<a href="/information/affix/'. $affixId.'" target="_blank">  <i class="fas fa-external-link-alt"></i> '.$row->name . '</a>';
            })->html(),

            Column::make('Type')->searchable(),

            Column::make('Damage Mod', 'base_damage_mod')->sortable()->format(function ($value) {
                return ($value * 100) . '%';
            }),
            Column::make('AC Mod', 'base_ac_mod')->sortable()->format(function ($value) {
                return ($value * 100) . '%';
            }),
            Column::make('Healing Mod', 'base_healing_mod')->sortable()->format(function ($value) {
                return ($value * 100) . '%';
            }),
            Column::make('Int Required', 'int_required')->sortable()->format(function ($value) {
                return number_format($value);
            }),
            Column::make('Cost')->sortable()->format(function ($value) {
                return number_format($value);
            }),
            Column::make('Min Enchanting Lv.', 'skill_level_required')->sortable(),
            Column::make('Trivial Enchanting Lv.', 'skill_level_trivial')->sortable(),
        ];
    }
}
