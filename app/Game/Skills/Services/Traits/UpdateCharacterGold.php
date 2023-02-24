<?php

namespace App\Game\Skills\Services\Traits;

use App\Game\Core\Events\UpdateTopBarEvent;
use App\Flare\Models\Character;
use App\Flare\Models\Item;
use Exception;

trait UpdateCharacterGold {

    /**
     * Update the characters gold when enchanting.
     *
     * Subtract cost from gold.
     *
     * @param Character $character
     * @param int $cost
     * @return void
     */
    public function updateCharacterGold(Character $character, int $cost): void {
        $character->update([
            'gold' => $character->gold - $cost,
        ]);

        event(new UpdateTopBarEvent($character->refresh()));
    }

    /**
     * Update character copper coins and gold dust.
     *
     * @param Character $character
     * @param Item $item
     * @return void
     * @throws Exception
     */
    public function updateTrinketCost(Character $character, Item $item): void {

        $copperCoinCost = $item->copper_coin_cost;
        $goldDustCost   = $item->gold_dust_cost;

        if ($character->classType()->isMerchant()) {
            $copperCoinCost = $copperCoinCost - $copperCoinCost * 0.10;
            $goldDustCost   = $goldDustCost   - $goldDustCost * 0.10;
        }

        $character->update([
            'copper_coins'  => $character->copper_coins - $copperCoinCost,
            'gold_dust'     => $character->gold_dust - $goldDustCost,
        ]);

        event(new UpdateTopBarEvent($character->refresh()));
    }

    /**
     * Update the alchemy currencies
     *
     * @param Character $character
     * @param Item $item
     * @throws Exception
     */
    public function updateAlchemyCost(Character $character, Item $item): void {
        $goldDustCost = $item->gold_dust_cost;
        $shardsCost   = $item->shards_cost;

        if ($character->classType()->isMerchant()) {
            $goldDustCost = $goldDustCost - $goldDustCost * 0.10;
            $shardsCost   = $shardsCost - $shardsCost * 0.10;
        }

        $character->update([
            'gold_dust'  => ($character->gold_dust - $goldDustCost),
            'shards'     => ($character->shards - $shardsCost),
        ]);

        event(new UpdateTopBarEvent($character->refresh()));
    }
}
