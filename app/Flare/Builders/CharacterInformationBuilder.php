<?php

namespace App\Flare\Builders;

use App\Flare\Models\Character;
use App\Flare\Models\Item;
use App\Flare\Models\ItemAffix;
use App\Flare\Traits\ClassBasedBonuses;
use App\Flare\Values\CharacterClassValue;
use App\Flare\Values\ItemEffectsValue;
use App\Flare\Values\ItemUsabilityType;
use Illuminate\Support\Collection;

class CharacterInformationBuilder {

    use ClassBasedBonuses;

    /**
     * @var Character $character
     */
    private $character;

    /**
     * @var Illuminate\Support\Collection $inventory
     */
    private $inventory;

    /**
     * Set the character and fetch it's inventory.
     *
     * @param Character $character
     * @return CharactrInformationBuilder
     */
    public function setCharacter(Character $character): CharacterInformationBuilder {
        $this->character = $character;

        $this->inventory = $character->inventory->slots->where('equipped', true);

        return $this;
    }

    public function getCharacter(): Character {
        return $this->character;
    }

    /**
     * Get the characters total stat mode for a stat
     *
     * Applies all bonuses to that stat based on equipped items in the
     * inventory assuming the user has anything equipped at all.
     *
     * @param Character $character
     * @return mixed
     */
    public function statMod(string $stat) {
        $base = $this->character->{$stat};

        $equipped = $this->fetchInventory()->filter(function($slot) {
            return $slot->equipped;
        });

        if ($equipped->isEmpty()) {
            return $base;
        }

        foreach ($equipped as $slot) {
            $base += $base * $this->fetchModdedStat($stat, $slot->item);
        }

        if ($this->character->boons->isNotEmpty()) {
            $boons = $this->character->boons()->where('type', ItemUsabilityType::STAT_INCREASE)->get();

            if ($boons->isNotEmpty()) {
                $sum = $boons->sum('stat_bonus');

                $base += $base + $base * $sum;
            }
        }

        return $base;
    }

    /**
     * Return the highest class bonus affix amount.
     *
     * Class bonuses do not stack, therefore we only return the highest valued
     * version.
     *
     * @return float
     */
    public function classBonus(): float {
        $slots = $this->fetchInventory()->filter(function($slot) {
           if (!is_null($slot->item->itemPrefix))  {
               if ($slot->item->itemPrefix->class_bonus > 0) {
                   return $slot;
               }
           }

            if (!is_null($slot->item->itemSuffix))  {
                if ($slot->item->itemSuffix->class_bonus > 0) {
                    return $slot;
                }
            }
        });

        $values = [];

        foreach ($slots as $slot) {
            if (!is_null($slot->item->itemPrefix))  {
                $values[] = $slot->item->itemPrefix->class_bonus;
            }

            if (!is_null($slot->item->itemSuffix))  {
                $values[] = $slot->item->itemSuffix->class_bonus;
            }
        }

        return empty($values) ? 0.0 : max($values);
    }

    /**
     * Find the prefix that reduces stats.
     *
     * We take the first one. It makes it easier than trying to figure out
     * which one is better.
     *
     * These cannot stack.
     *
     * @return ItemAffix|null
     */
    public function findPrefixStatReductionAffix(): ?ItemAffix {
        $slot = $this->fetchInventory()->filter(function($slot) {
            if (!is_null($slot->item->itemPrefix))  {
                if ($slot->item->itemPrefix->reduces_enemy_stats) {
                    return $slot;
                }
            }
        })->first();

        if (!is_null($slot)) {
            return $slot->item->itemPrefix;
        }

        return null;
    }

    /**
     * Returns a collection of single stat reduction affixes.
     *
     * @return Collection
     */
    public function findSuffixStatReductionAffixes(): Collection {
        return $this->fetchInventory()->filter(function($slot) {
            if (!is_null($slot->item->itemSuffix))  {
                if ($slot->item->itemSuffix->reduces_enemy_stats) {
                    return $slot;
                }
            }
        });
    }

    /**
     * Build the attack
     *
     * Fetches the damage stat with all modifications and applies all skill bonuses.
     *
     * @return int
     */
    public function buildAttack(): int {

        $characterDamageStat = $this->statMod($this->character->damage_stat);
        $classBonuses        = $this->getFightersDamageBonus($this->character) +
            $this->prophetDamageBonus($this->character) +
            $this->getThievesDamageBonus($this->character) +
            $this->getVampiresDamageBonus($this->character) +
            $this->getRangersDamageBonus($this->character);

        $characterDamageStat = $characterDamageStat + $characterDamageStat * $this->fetchSkillAttackMod();

        $totalAttack = $this->getWeaponDamage();

        return round($characterDamageStat + ($totalAttack + $totalAttack * $classBonuses));
    }

    /**
     * Builds Total Attack.
     *
     * @return int
     * @throws \Exception
     */
    public function buildTotalAttack(): int {

        $characterDamageStat = $this->statMod($this->character->damage_stat);
        $classBonuses        = $this->getFightersDamageBonus($this->character) +
            $this->prophetDamageBonus($this->character) +
            $this->getThievesDamageBonus($this->character) +
            $this->getVampiresDamageBonus($this->character) +
            $this->getRangersDamageBonus($this->character);

        $characterDamageStat = $characterDamageStat + $characterDamageStat * $this->fetchSkillAttackMod();

        $totalAttack = $this->getWeaponDamage() + $this->getSpellDamage() + $this->getTotalArtifactDamage() + $this->getTotalRingDamage();

        return round($characterDamageStat + ($totalAttack + $totalAttack * $classBonuses));
    }

    /**
     * Build the defence
     *
     * Fetches the defence based off a base of ten plus the equipment, skills and other
     * bonuses.
     *
     * @return int
     */
    public function buildDefence(): int {
        return round((10 + $this->getDefence()) * (1 + $this->fetchSkillACMod() + $this->getFightersDefence($this->character)));
    }

    /**
     * Build the heal for
     *
     * Fetches the total healing amount based on skills and equipment.
     *
     * @return int
     * @throws \Exception
     */
    public function buildHealFor(): int {
        $classBonus    = $this->prophetHealingBonus($this->character) + $this->getVampiresHealingBonus($this->character);

        $classType     = new CharacterClassValue($this->character->class->name);

        $healingAmount = $this->fetchHealingAmount();
        $dmgStat       = $this->character->class->damage_stat;

        if ($classType->isVampire()) {
            $healingAmount += $this->character->{$dmgStat} - $this->character->{$dmgStat} * .025;
        }

        if ($classType->isProphet()) {
            $hasHealingSpells = $this->prophetHasHealingSpells($this->character);

            if ($hasHealingSpells) {
                $healingAmount += $this->character->{$dmgStat} * .025;
            }
        }

        return round($healingAmount + ($healingAmount * ($this->fetchSkillHealingMod() + $classBonus)));
    }

    /**
     * Fetch the resurrection chance;
     *
     * @return float
     * @throws \Exception
     */
    public function fetchResurrectionChance(): float {
        $resurrectionItems = $this->fetchInventory()->filter(function($slot) {
            return $slot->item->can_resurrect;
        });

        $chance    = 0.0;
        $classType = new CharacterClassValue($this->character->class->name);

        if ($classType->isVampire() || $classType->isProphet()) {
            $chance += 0.05;
        }

        if ($resurrectionItems->isEmpty()) {
             return $chance;
        }

        $chance += $resurrectionItems->sum('item.resurrection_chance');

        return $chance;
    }

    /**
     * Build total health
     *
     * Build the characters health based off equipment, plus the characters health and
     * a base of 10.
     *
     * @return int
     */
    public function buildHealth(): int {

        if ($this->character->is_dead) {
            return 0;
        }

        $baseHealth = $this->character->dur + 10;

        foreach ($this->fetchInventory() as $slot) {
            if ($slot->equipped) {
                $percentage = $slot->item->getTotalPercentageForStat('dur');

                $baseHealth += $baseHealth * $percentage;
            }
        }

        return $baseHealth;
    }

    /**
     * Does the character have any artifacts?
     *
     * @return bool
     */
    public function hasArtifacts(): bool {
        return $this->fetchInventory()->filter(function ($slot) {
            return $slot->item->type === 'artifact' && $slot->equipped;
        })->isNotEmpty();
    }

    /**
     * Does the character have any items with affixes?
     *
     * @return bool
     */
    public function hasAffixes(): bool {
        return $this->fetchInventory()->filter(function ($slot) {
            return ((!is_null($slot->item->itemPrefix)) || (!is_null($slot->item->itemSuffix))) && $slot->equipped;
        })->isNotEmpty();
    }

    /**
     * Can your affixes be resisted at all?
     *
     * If you have the quest item that has the AFFIXES_IRRESISTIBLE
     * effect, then you cannot be resisted for affixes.
     *
     * @return bool
     */
    public function canAffixesBeResisted(): bool {
        return $this->character->inventory->slots->filter(function($slot) {
            return $slot->item->type === 'quest' && $slot->item->effect === ItemEffectsValue::AFFIXES_IRRESISTIBLE;
        })->isEmpty();
    }

    /**
     * Determine the affix damage.
     *
     * Some affixes cannot stack their damage, so we only return the highest if you pass in false.
     *
     * If you want the stacking ones only, then this will return the total value of those.
     *
     * Fetches from both prefix and suffix.
     *
     * @param bool $canStack
     * @return int
     */
    public function getTotalAffixDamage(bool $canStack = true): int {
        $slots = $this->fetchInventory()->filter(function ($slot) use ($canStack) {

            if (!is_null($slot->item->itemPrefix) && $slot->equipped) {

                if ($canStack) {
                    if ($slot->item->itemPrefix->damage > 0 && $slot->item->itemPrefix->damage_can_stack) {
                        return $slot;
                    }
                } else {
                    if ($slot->item->itemPrefix->damage > 0 && !$slot->item->itemPrefix->damage_can_stack) {
                        return $slot;
                    }
                }

            }

            if (!is_null($slot->item->itemSuffix) && $slot->equipped) {
                if ($canStack) {
                    if ($slot->item->itemSuffix->damage > 0 && $slot->item->itemSuffix->damage_can_stack) {
                        return $slot;
                    }
                } else {
                    if ($slot->item->itemSuffix->damage > 0 && !$slot->item->itemSuffix->damage_can_stack) {
                        return $slot;
                    }
                }
            }
        });

        $totalResistibleDamage = 0;

        if ($canStack) {
            foreach ($slots as $slot) {
                if (!is_null($slot->item->itemPrefix)) {
                    $totalResistibleDamage += $slot->item->itemPrefix->damage;
                }

                if (!is_null($slot->item->itemSuffix)) {
                    $totalResistibleDamage += $slot->item->itemSuffix->damage;
                }
            }
        } else {
            $totalHighestPrefix = $this->getHighestDamageValueFromAffixes($slots, 'itemPrefix');
            $totalHighestSuffix = $this->getHighestDamageValueFromAffixes($slots, 'itemSuffix');

            if ($totalHighestPrefix > $totalHighestSuffix) {
                return $totalHighestPrefix;
            }

            $totalResistibleDamage = $totalHighestSuffix;
        }



        return $totalResistibleDamage;
    }

    /**
     * Does the character have any damage spells
     *
     * @return bool
     */
    public function hasDamageSpells(): bool {
        return $this->fetchInventory()->filter(function ($slot) {
            return $slot->item->type === 'spell-damage' && $slot->equipped;
        })->isNotEmpty();
    }

    /**
     * Get the total Spell Damage
     *
     * @return int
     */
    public function getTotalSpellDamage(): int {
        return $this->getSpellDamage();
    }

    /**
     * Get the total artifact damage.
     *
     * @return int
     */
    public function getTotalArtifactDamage(): int {
        return $this->getArtifactDamage();
    }

    /**
     * Gets the total ring damage.
     *
     * @return int
     */
    public function getTotalRingDamage(): int {
        return $this->getRingDamage();
    }

    /**
     * Get total annulment
     *
     * @return float
     */
    public function getTotalAnnulment(): float {
        return $this->getArtifactAnnulment();
    }

    /**
     * Get total spell evasion
     *
     * @return float
     */
    public function getTotalSpellEvasion(): float {
        return  $this->getSpellEvasion();
    }

    protected function getHighestDamageValueFromAffixes(Collection $slots, string $suffixType): int {
        $values = [];

        foreach ($slots as $slot) {
            if (!is_null($slot->item->{$suffixType})) {
                if ($slot->item->{$suffixType}->damage > 0) {
                    $values[] = $slot->item->{$suffixType}->damage;
                }
            }
        }

        if (empty($values)) {
            return 0;
        }

        return max($values);
    }

    protected function getSpellEvasion(): float {
        $skillSpellEvasion = 0.0;

        $skill = $this->character->skills->filter(function($skill) {
            return $skill->type()->isSpellEvasion();
        })->first();

        if (!is_null($skill)) {
            $skillSpellEvasion = $skill->skill_bonus;
        }

        $itemsEvasion = $this->fetchInventory()->filter(function ($slot) {
            return $slot->item->type === 'ring' && $slot->equipped;
        })->sum('item.spell_evasion');

        return $itemsEvasion + $skillSpellEvasion;
    }

    protected function getArtifactAnnulment(): float {
        $skillArtifactAnnulment = 0.0;

        $skill = $this->character->skills->filter(function($skill) {
            return $skill->type()->isArtifactAnnulment();
        })->first();

        if (!is_null($skill)) {
            $skillArtifactAnnulment = $skill->skill_bonus;
        }

        $itemsEvasion = $this->fetchInventory()->filter(function ($slot) {
            return $slot->item->type === 'ring' && $slot->equipped;
        })->sum('item.artifact_annulment');

        return $itemsEvasion + $skillArtifactAnnulment;
    }

    protected function fetchSkillAttackMod(): float {
        $percentageBonus = 0.0;

        $skills = $this->character->skills->filter(function($skill) {
            return is_null($skill->baseSkill->game_class_id);
        })->all();

        foreach ($skills as $skill) {
            $percentageBonus += $skill->base_damage_mod;
        }

        return $percentageBonus;
    }

    protected function fetchSkillHealingMod(): float {
        $percentageBonus = 0.0;

        $skills = $this->character->skills->filter(function($skill) {
            return is_null($skill->baseSkill->game_class_id);
        })->all();

        foreach ($skills as $skill) {
            $percentageBonus += $skill->base_healing_mod;
        }

        return $percentageBonus;
    }

    protected function fetchSkillACMod(): float {
        $percentageBonus = 0.0;

        $skills = $this->character->skills->filter(function($skill) {
            return is_null($skill->baseSkill->game_class_id);
        })->all();

        foreach ($skills as $skill) {
            $percentageBonus += $skill->base_ac_mod;
        }

        return $percentageBonus;
    }

    protected function getWeaponDamage(): int {
        $damage = 0;

        foreach ($this->fetchInventory() as $slot) {
            if ($slot->item->type === 'weapon') {
                $damage += $slot->item->getTotalDamage();
            }
        }

        return $damage;
    }

    protected function getSpellDamage(): int {
        $damage = 0;

        foreach ($this->fetchInventory() as $slot) {
            if ($slot->item->type === 'spell-damage') {
                $damage += $slot->item->getTotalDamage();
            }
        }

        $bonus = $this->hereticSpellDamageBonus($this->character);

        if ($bonus < 2) {
            $bonus += 1;
        }

        return $damage * $bonus;
    }

    protected function getArtifactDamage(): int {
        $damage = 0;

        foreach ($this->fetchInventory() as $slot) {
            if ($slot->item->type === 'artifact') {
                $damage += $slot->item->getTotalDamage();
            }
        }

        return $damage;
    }

    protected function getRingDamage(): int {
        $damage = 0;

        foreach ($this->fetchInventory() as $slot) {
            if ($slot->item->type === 'ring') {
                $damage += $slot->item->getTotalDamage();
            }
        }

        return $damage;
    }

    protected function getDefence(): int {
        $defence = 0;

        foreach ($this->fetchInventory() as $slot) {

            $defence += $slot->item->getTotalDefence();
        }

        if ($defence !== 10) {
            return $defence / 6;
        }

        return $defence;
    }

    protected function fetchHealingAmount(): int {
        $healFor = 0;

        foreach ($this->fetchInventory() as $slot) {
            $healFor += $slot->item->getTotalHealing();
        }

        return $healFor;
    }

    /**
     * Fetch the appropriate inventory.
     *
     * Either return the current inventory, by default, if not empty or
     * return the inventory set that is currently equipped.
     *
     * Players cannot have both equipped at the same time.
     *
     * @return Collection
     */
    protected function fetchInventory(): Collection
    {
        if ($this->inventory->isNotEmpty()) {
            return $this->inventory;
        }

        $inventorySet = $this->character->inventorySets()->where('is_equipped', true)->first();

        if (!is_null($inventorySet)) {
            return $inventorySet->slots;
        }

        return $this->inventory;
    }

    protected function fetchModdedStat(string $stat, Item $item): float {
        $staMod          = $item->{$stat . '_mod'};
        $totalPercentage = !is_null($staMod) ? $staMod : 0.0;

        if (!is_null($item->itemPrefix)) {
            $prefixMod        = $item->itemPrefix->{$stat . '_mod'};
            $totalPercentage += !is_null($prefixMod) ? $prefixMod : 0.0;
        }

        if (!is_null($item->itemSuffix)) {
            $suffixMod        = $item->itemSuffix->{$stat . '_mod'};
            $totalPercentage += !is_null($suffixMod) ? $suffixMod : 0.0;
        }

        return  $totalPercentage;
    }
}
