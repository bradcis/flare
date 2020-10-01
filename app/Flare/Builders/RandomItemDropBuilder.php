<?php

namespace App\Flare\Builders;

use Illuminate\Database\Eloquent\Collection;
use App\Flare\Models\Item;
use App\Flare\Models\Character;
use App\Flare\Models\ItemAffix;

class RandomItemDropBuilder {

    private $itemAffixes; 

    public function setItemAffixes(Collection $itemAffixes): RandomItemDropBuilder {
        $this->itemAffixes = $itemAffixes;

        return $this;
    }

    public function generateItem(Character $character): Item {
        $item          = Item::inRandomOrder()->with(['itemSuffix', 'itemPrefix'])->where('type', '!=', 'artifact')->where('type', '!=', 'quest')->get()->first();
        $duplicateItem = $this->duplicateItem($item);

        if ($this->shouldHaveItemAffix($character)) {
            $affix = $this->fetchRandomItemAffix();

            if (is_null($affix)) {
                $duplicateItem->delete();

                return $item;
            }
            
            if (!is_null($duplicateItem->itemSuffix) || !is_null($duplicateItem->itemPrefix)) {
                $hasSameAffix = $this->hasSameAffix($duplicateItem, $affix);
                
                if ($hasSameAffix) {
                    $duplicateItem->delete();

                    return $item;
                } else {
                    $this->attachAffix($duplicateItem, $affix);
                }
            } else {
                $this->attachAffix($duplicateItem, $affix);
            }
        } else {
            $duplicateItem->delete();

            return $item;
        }

        return $duplicateItem->refresh();
    }

    protected function duplicateItem(Item $item): Item {
        $duplicateItem = $item->replicate();
        $duplicateItem->save();
 
        if (!is_null($item->itemSuffix)) {
            $duplicateItem->update([
                'item_suffix_id' => $item->itemSuffix->id,
            ]);
        }

        if (!is_null($item->itemPrefix)) {
            $duplicateItem->update([
                'item_prefix_id' => $item->itemPrefix->id,
            ]);
        }

        return $duplicateItem->refresh()->load(['itemSuffix', 'itemPrefix']);
    }

    protected function hasSameAffix(Item $duplicateItem, ItemAffix $affix): bool {
        $foundAffix = $duplicateItem->{'item'.ucFirst($affix->type)};

        if (is_null($foundAffix)) {
            return false;
        }

        return $foundAffix->name === $affix->name;
    }

    protected function attachAffix(Item $item, ItemAffix $itemAffix): Item {
        $item->update(['item_'.$itemAffix->type.'_id' => $itemAffix->id]);

        return $item->refresh();
    }

    protected function shouldHaveItemAffix(Character $character): bool {
        $lootingChance = $character->skills->where('name', '=', 'Looting')->first()->skill_bonus;

        return (rand(1, 100) + $lootingChance) > 50;
    }

    protected function fetchRandomItemAffix() {
        $index = count($this->itemAffixes) - 1;

        if ($index !== -1) {
            return $this->itemAffixes[rand(0, $index)];
        }

        return null;
    }
}
