<?php

namespace App\Flare\Transformers;

use App\Flare\Models\GameBuildingUnit;
use App\Game\Kingdoms\Values\KingdomMaxValue;
use App\Game\Kingdoms\Values\UnitCosts;
use League\Fractal\TransformerAbstract;
use App\Flare\Models\GameUnit;

class UnitTransformer extends TransformerAbstract {

    /**
     * Gets the response data for the character sheet
     *
     * @param Character $character
     * @return mixed
     */
    public function transform(GameUnit $unit) {
        return [
            'id'                      => $unit->id,
            'name'                    => $unit->name,
            'description'             => $unit->description,
            'attack'                  => $unit->attack,
            'defence'                 => $unit->defence,
            'can_heal'                => $unit->can_heal,
            'heal_percentage'         => $unit->heal_percentage,
            'siege_weapon'            => $unit->siege_weapon,
            'attacker'                => $unit->attacker,
            'defender'                => $unit->defender,
            'travel_time'             => $unit->travel_time,
            'wood_cost'               => $unit->wood_cost,
            'clay_cost'               => $unit->clay_cost,
            'stone_cost'              => $unit->stone_cost,
            'iron_cost'               => $unit->iron_cost,
            'required_population'     => $unit->required_population,
            'time_to_recruit'         => $unit->time_to_recruit,
            'current_amount'          => $unit->kingdom_current_amount,
            'max_amount'              => KingdomMaxValue::MAX_UNIT,
            'cost_per_unit'           => (new UnitCosts($unit->name))->fetchCost(),
            'pop_cost_gold'           => (new UnitCosts(UnitCosts::PERSON))->fetchCost(),
            'recruited_from'          => GameBuildingUnit::where('game_unit_id', $unit->id)->first()->gameBuilding,
            'required_building_level' => GameBuildingUnit::where('game_unit_id', $unit->id)->first()->required_level,
        ];
    }
}
