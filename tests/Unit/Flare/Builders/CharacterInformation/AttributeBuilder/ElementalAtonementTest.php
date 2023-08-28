<?php

namespace Tests\Unit\Flare\Builders\CharacterInformation\AttributeBuilder;


use Tests\TestCase;
use Tests\Traits\CreateItem;
use Tests\Traits\CreateClass;
use Tests\Traits\CreateGameMap;
use Tests\Traits\CreateGameSkill;
use Tests\Traits\CreateItemAffix;
use Tests\Setup\Character\CharacterFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Flare\Builders\CharacterInformation\CharacterStatBuilder;
use Tests\Traits\CreateGem;

class ElementalAtonementTest extends TestCase {

    use RefreshDatabase, CreateItem, CreateItemAffix, CreateGameMap, CreateClass, CreateGameSkill, CreateGem;

    private ?CharacterFactory $character;

    private ?CharacterStatBuilder $characterStatBuilder;

    public function setUp(): void {
        parent::setUp();

        $this->character            = (new CharacterFactory())->createBaseCharacter()->assignSkill(
            $this->createGameSkill([
                'class_bonus' => 0.01
            ]),
            5
        )->givePlayerLocation();

        $this->characterStatBuilder = resolve(CharacterStatBuilder::class);
    }

    public function testCharacterWithMaxedOutElementalAtonement() {
        $item = $this->createItem([
            'type' => 'weapon',
            'socket_count' => 1,
        ]);

        $item->sockets()->create([
            'item_id' => $item->id,
            'gem_id'  => $this->createGem([
                'primary_atonement_amount'   => 0.75,
                'secondary_atonement_amount' => 0.75,
                'tertiary_atonement_amount'  => 0.75,
            ])->id
        ]);

        $item = $item->refresh();

        $character = $this->character->inventoryManagement()->giveItem($item, true)->getCharacter();

        $statBuilder = $this->characterStatBuilder->setCharacter($character);

        $elementalData = $statBuilder->buildElementalAtonement();

        $this->assertNotEmpty($elementalData['highest_element']);
        $this->assertEquals('Fire', $elementalData['highest_element']['name']);
        $this->assertEquals(0.75, $elementalData['highest_element']['damage']);
        $this->assertNotEmpty($elementalData['elemental_data']);
    }
}
