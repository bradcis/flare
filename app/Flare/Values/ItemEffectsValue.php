<?php

namespace App\Flare\Values;

class ItemEffectsValue {

    /**
     * @var string $value
     */
    private $value;

    const WALK_ON_WATER         = 'walk-on-water';
    const WALK_ON_DEATH_WATER   = 'walk-on-death-water';
    const LABYRINTH             = 'labyrinth';
    const DUNGEON               = 'dungeon';
    const SHADOWPLANE           = 'shadow-plane';
    const HELL                  = 'hell';
    const TELEPORT_TO_CELESTIAL = 'teleport-to-celestial';
    const AFFIXES_IRRESISTIBLE  = 'affixes-irresistible';
    const CONTNUE_LEVELING      = 'continue-leveling';
    const GOLD_DUST_RUSH        = 'gold-dust-rush';
    const MASS_EMBEZZLE         = 'mass-embezzle';
    const WALK_ON_MAGMA         = 'walk-on-magma';

    /**
     * @var string[] $values
     */
    protected static $values = [
        self::WALK_ON_WATER         => 'walk-on-water',
        self::WALK_ON_DEATH_WATER   => 'walk-on-death-water',
        self::WALK_ON_MAGMA         => 'walk-on-magma',
        self::LABYRINTH             => 'labyrinth',
        self::DUNGEON               => 'dungeon',
        self::SHADOWPLANE           => 'shadow-plane',
        self::HELL                  => 'hell',
        self::TELEPORT_TO_CELESTIAL => 'teleport-to-celestial',
        self::AFFIXES_IRRESISTIBLE  => 'affixes-irresistible',
        self::CONTNUE_LEVELING      => 'continue-leveling',
        self::GOLD_DUST_RUSH        => 'gold-dust-rush',
        self:: MASS_EMBEZZLE        => 'mass-embezzle',
    ];

    /**
     * ItemEffectsValue constructor.
     *
     * Throws if the value does not exist in the array of const values.
     *
     * @param string $value
     * @throws \Exception
     */
    public function __construct(string $value) {

        if (!in_array($value, self::$values)) {
            throw new \Exception($value . ' does not exist.');
        }

        $this->value = $value;
    }

    /**
     * is walk on water?
     *
     * @return bool
     */
    public function walkOnWater(): bool {
        return $this->value === self::WALK_ON_WATER;
    }

    /**
     * Is Walk on death water?
     *
     * @return bool
     */
    public function walkOnDeathWater(): bool {
        return $this->value === self::WALK_ON_DEATH_WATER;
    }

    /**
     * is Walk on Magma?
     *
     * @return bool
     */
    public function walkOnMagma(): bool {
        return $this->value === self::WALK_ON_MAGMA;
    }

    /**
     * Can Access Labyrinth
     *
     * @return bool
     */
    public function labyrinth(): bool {
        return $this->value === self::LABYRINTH;
    }

    /**
     * Can access Dungeon
     *
     * @return bool
     */
    public function dungeon(): bool {
        return $this->value === self::DUNGEON;
    }


    /**
     * Can access Shadow plane
     *
     * @return bool
     */
    public function shadowPlane(): bool {
        return $this->value === self::SHADOWPLANE;
    }

    /**
     * Can Access Hell
     *
     * @return bool
     */
    public function hell(): bool {
        return $this->value === self::HELL;
    }

    /**
     * Does this item make your affixes irresistible to the enemy?
     *
     * @return bool
     */
    public function areAffixesIrresistible(): bool {
        return $this->value === self::AFFIXES_IRRESISTIBLE;
    }

    /**
     * does this item allow for a gold dust rush?
     *
     * @return bool
     */
    public function isGoldDustRush(): bool {
        return $this->value === self::GOLD_DUST_RUSH;
    }

    /**
     * Can we teleport to celestial?
     *
     * @return bool
     */
    public function teleportToCelestial(): bool {
        return $this->value === self::TELEPORT_TO_CELESTIAL;
    }

    /**
     * Can mass embezzle?
     *
     * @return bool
     */
    public function canMassEmbezzle(): bool {
        return $this->value === self::MASS_EMBEZZLE;
    }
}
