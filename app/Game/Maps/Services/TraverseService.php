<?php

namespace App\Game\Maps\Services;

use App\Flare\Events\UpdateTopBarEvent;
use App\Flare\Models\Location;
use App\Flare\Services\BuildCharacterAttackTypes;
use App\Game\Core\Events\UpdateAttackStats;
use App\Game\Maps\Events\MoveTimeOutEvent;
use App\Game\Maps\Events\UpdateGlobalCharacterCountBroadcast;
use App\Game\Maps\Values\MapTileValue;
use App\Game\Messages\Events\GlobalMessageEvent;
use Illuminate\Support\Facades\Cache;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Facades\App\Flare\Cache\CoordinatesCache;
use App\Game\Maps\Events\UpdateMapBroadcast;
use App\Game\Maps\Events\UpdateActionsBroadcast;
use App\Flare\Events\ServerMessageEvent;
use App\Game\Messages\Events\ServerMessageEvent as MessageEvent;
use App\Flare\Models\GameMap;
use App\Flare\Models\Character;
use App\Flare\Transformers\CharacterAttackTransformer;
use App\Flare\Transformers\MonsterTransfromer;
use App\Flare\Values\ItemEffectsValue;
use League\Fractal\Resource\Item as ResourceItem;

class TraverseService {

    /**
     * @var Manager $manager
     */
    private $manager;

    /**
     * @var CharacterAttackTransformer $characterAttackTransformer
     */
    private $characterAttackTransformer;

    /**
     * @var MonsterTransfromer $monsterTransformer
     */
    private $monsterTransformer;

    /**
     * @var LocationService $locationService
     */
    private $locationService;

    /**
     * @var MapTileValue $mapTileValue
     */
    private $mapTileValue;

    private $buildCharacterAttackTypes;

    /**
     * TraverseService constructor.
     *
     * @param Manager $manager
     * @param CharacterAttackTransformer $characterAttackTransformer
     * @param MonsterTransfromer $monsterTransformer
     * @param LocationService $locationService
     */
    public function __construct(
        Manager $manager,
        CharacterAttackTransformer $characterAttackTransformer,
        BuildCharacterAttackTypes $buildCharacterAttackTypes,
        MonsterTransfromer $monsterTransformer,
        LocationService $locationService,
        MapTileValue $mapTileValue
    ) {
        $this->manager                    = $manager;
        $this->characterAttackTransformer = $characterAttackTransformer;
        $this->buildCharacterAttackTypes  = $buildCharacterAttackTypes;
        $this->monsterTransformer         = $monsterTransformer;
        $this->locationService            = $locationService;
        $this->mapTileValue               = $mapTileValue;
    }

    /**
     * Can you travel to another plane?
     *
     * @param int $mapId
     * @param Character $character
     * @return bool
     */
    public function canTravel(int $mapId, Character $character): bool {
        $gameMap = GameMap::find($mapId);

        if ($gameMap->name === 'Labyrinth') {
            $hasItem = $character->inventory->slots->filter(function($slot) {
                return $slot->item->effect === ItemEffectsValue::LABYRINTH;
            })->all();

            return !empty($hasItem);
        }

        if ($gameMap->name === 'Dungeons') {
            $hasItem = $character->inventory->slots->filter(function($slot) {
                return $slot->item->effect === ItemEffectsValue::DUNGEON;
            })->all();

            return !empty($hasItem);
        }

        if ($gameMap->name === 'Shadow Plane') {
            $hasItem = $character->inventory->slots->filter(function($slot) {
                return $slot->item->effect === ItemEffectsValue::SHADOWPLANE;
            })->all();

            return !empty($hasItem);
        }

        if ($gameMap->name === 'Hell') {
            $hasItem = $character->inventory->slots->filter(function($slot) {
                return $slot->item->effect === ItemEffectsValue::HELL;
            })->all();

            return !empty($hasItem);
        }

        if ($gameMap->name === 'Surface') {
            return true;
        }

        return false;
    }

    /**
     * Travel to another plane of existence.
     *
     * @param int $mapId
     * @param Character $character
     */
    public function travel(int $mapId, Character $character) {
        $oldMap = $character->map->game_map_id;

        $character->map()->update([
            'game_map_id' => $mapId
        ]);

        $character = $character->refresh();

        $xPosition = $character->map->character_position_x;
        $yPosition = $character->map->character_position_y;

        $cache = CoordinatesCache::getFromCache();

        $character = $this->changeLocation($character, $cache);

        $newXPosition = $character->map->character_position_x;
        $newYPosition = $character->map->character_position_y;

        // @codeCoverageIgnoreStart
        //
        // Ignore this aspect as it's really hard to mock without messing up the tile value mock.
        if ($newXPosition !== $xPosition || $newYPosition !== $yPosition) {

            $color = $this->mapTileValue->getTileColor($character, $xPosition, $yPosition);

            if ($this->mapTileValue->isWaterTile($color) || $this->mapTileValue->isDeathWaterTile($color)) {
                event(new ServerMessageEvent($character->user, 'moved-location', 'Your character was moved as you are missing the appropriate quest item.'));
            }
        }
        // @codeCoverageIgnoreEnd

        $this->updateGlobalCharacterMapCount($oldMap);
        $this->updateMap($character);
        $this->updateActions($mapId, $character);
        $this->updateCharacterTimeOut($character);

        $message = 'You have traveled to: ' . $character->map->gameMap->name;

        event(new ServerMessageEvent($character->user, 'plane-transfer', $message));

        $gameMap = $character->map->gameMap;

        if ($character->map->gameMap->mapType()->isShadowPlane()) {
            $message = 'As you enter into the Shadow Plane, all you see for miles around are 
            shadowy figures moving across the land. The color of the land is grey and lifeless. But you 
            feel the presence of death as it creeps ever closer. 
            (Characters can walk on water here, monster strength is increased by '.($gameMap->enemy_stat_bonus * 100).'% including Devouring Light. You are reduced by '.($gameMap->enemy_stat_bonus * 100).'% (Damage wise) while here.)';

            event(new MessageEvent($character->user,  $message));

            event(new GlobalMessageEvent('The gates have opened for: ' . $character->name . '. They have entered the realm of shadows!'));
        }

        if ($character->map->gameMap->mapType()->isHell()) {
            $message = 'The stench of sulfur fills your nose. The heat of the magma oceans bathes over you. Demonic shadows and figures move about the land. Monsters are increased by: ' .
                ($gameMap->enemy_stat_bonus * 100) . '% while you are reduced by: '.
                ($gameMap->character_attack_reduction * 100) . '% in both (modified) stats and damage done. Any quest items that make 
                affixes irresistible will not work down here. Finally, all life stealing affixes be they stackable or not are reduced to half their total output for damage.';

            event(new MessageEvent($character->user,  $message));

            event(new GlobalMessageEvent('Hell\'s gates swing wide for: ' . $character->name . '. May the light of Argose the Angelic Saviour, be their guide through such darkness!'));
        }
    }

    protected function changeLocation(Character $character, array $cache) {

        if (!$this->mapTileValue->canWalkOnWater($character, $character->map->character_position_x, $character->map->character_position_y) ||
            !$this->mapTileValue->canWalkOnDeathWater($character, $character->map->character_position_x, $character->map->character_position_y)
        ) {

            $x = $cache['x'];
            $y = $cache['y'];

            $character->map()->update([
                'character_position_x' => $x[rand(0, count($x) - 1)],
                'character_position_y' => $y[rand(0, count($y) - 1)],
            ]);

            return $this->changeLocation($character->refresh(), $cache);
        }

        return $character->refresh();
    }

    /**
     * Set the timeout for the character.
     *
     * @param Character $character
     */
    protected function updateCharacterTimeOut(Character $character) {
        $character->update([
            'can_move'          => false,
            'can_move_again_at' => now()->addSeconds(10),
        ]);

        event(new MoveTimeOutEvent($character));
    }

    /**
     * Update character actions.
     *
     * @param int $mapId
     * @param Character $character
     */
    protected function updateActions(int $mapId, Character $character) {
        $user         = $character->user;
        $gameMap      = GameMap::find($mapId);
        $characterMap = $character->map;

        $locationWithEffect   = Location::whereNotNull('enemy_strength_type')
                                        ->where('x', $characterMap->character_position_x)
                                        ->where('y', $characterMap->character_position_y)
                                        ->where('game_map_id', $characterMap->game_map_id)
                                        ->first();

        if ($gameMap->mapType()->isShadowPlane() || $gameMap->mapType()->isHell()) {
            $this->updateAtctionTypeCache($character, $gameMap->enemy_stat_bonus);
        } else {
            $this->updateAtctionTypeCache($character, 0.0);
        }

        $characterData = new Item($character, $this->characterAttackTransformer);

        if (!is_null($locationWithEffect)) {
            $monsters  = Cache::get('monsters')[$locationWithEffect->name];
        } else {
            $monsters  = Cache::get('monsters')[GameMap::find($mapId)->name];
        }

        $characterData = $this->manager->createData($characterData)->toArray();

        broadcast(new UpdateActionsBroadcast($characterData, $monsters, $user));

        event(new UpdateAttackStats($characterData, $user));

        event(new UpdateTopBarEvent($character));
    }

    protected function updateAtctionTypeCache(Character $character, float $deduction) {
        resolve(BuildCharacterAttackTypes::class)->buildCache($character);

        $attackData = Cache::get('character-attack-data-' . $character->id);

        foreach ($attackData as $key => $array) {
            $attackData[$key]['damage_deduction'] = $deduction;
        }

        Cache::put('character-attack-data-' . $character->id, $attackData);
    }

    /**
     * Update the map to reflect the new plane.
     *
     * @param Character $character
     */
    protected function updateMap(Character $character) {
        broadcast(new UpdateMapBroadcast($this->locationService->getLocationData($character->refresh()), $character->user));
    }

    /**
     * When the character traverses, lets update the global character count for all planes.
     *
     * @param int $oldMap
     */
    protected function updateGlobalCharacterMapCount(int $oldMap) {
        $maps = GameMap::where('id', '=', $oldMap)->get();

        foreach ($maps as $map) {
            broadcast(new UpdateGlobalCharacterCountBroadcast($map));
        }

        $maps = GameMap::where('id', '!=', $oldMap)->get();

        foreach ($maps as $map) {
            broadcast(new UpdateGlobalCharacterCountBroadcast($map));
        }
    }
}
