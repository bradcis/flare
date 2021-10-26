<?php

namespace Tests\Feature\Game\Core;

use App\Flare\Models\Character;
use App\Flare\Models\Inventory;
use App\Flare\Models\InventorySet;
use App\Flare\Models\InventorySlot;
use App\Flare\Models\Item;
use App\Flare\Models\SetSlot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreateRole;
use Tests\Traits\CreateUser;
use Tests\Traits\CreateItem;
use Tests\Traits\CreateItemAffix;
use Tests\Setup\Character\CharacterFactory;

class CharacterInventoryControllerTest extends TestCase
{
    use RefreshDatabase,
        CreateItem,
        CreateItemAffix,
        CreateUser,
        CreateRole;

    private $character;

    public function setUp(): void {
        parent::setUp();

        $this->character = (new CharacterFactory)->createBaseCharacter()
                                                 ->givePlayerLocation()
                                                 ->equipStartingEquipment();
    }

    public function tearDown(): void {
        parent::tearDown();

        $this->character = null;
    }

    public function testCanEquipItem() {

        $user = $this->character->inventoryManagement()->unequipAll()->getCharacterFactory()->getUser();


        $this->actingAs($user)->post(route('game.equip.item', ['character' => $this->character->getCharacter()->id]), [
            'position'   => 'left-hand',
            'slot_id'    => InventorySlot::first()->id,
            'equip_type' => 'weapon',
        ])->response;

        $character = $this->character->getCharacter();

        $character->inventory->slots->each(function($slot) {
            $this->assertTrue($slot->equipped);
        });
    }


    public function testCannotEquipItemWhenCharacterDead() {

        $user = $this->character->updateCharacter(['is_dead' => true])
            ->inventoryManagement()
            ->unequipAll()
            ->getCharacterFactory()
            ->getUser();

        $response = $this->actingAs($user)->visitRoute('game')->post(route('game.equip.item', ['character' => $this->character->getCharacter()->id]), [
            'position'   => 'left-hand',
            'slot_id'    => InventorySlot::first()->id,
            'equip_type' => 'weapon',
        ])->response;

        $response->assertSessionHas('error', "You are dead and must revive before trying to do that. Dead people can't do things.");
    }

    public function testCannotEquipItemYouDontHave() {
        $user = $this->character->inventoryManagement()->unequipAll()->getCharacterFactory()->getUser();

        $response = $this->actingAs($user)->post(route('game.equip.item', ['character' => $this->character->getCharacter()->id]), [
            'position'   => 'left-hand',
            'slot_id'    => '7',
            'equip_type' => 'weapon',
        ])->response;

        $response->assertSessionHas('error', 'The item you are trying to equip as a replacement, does not exist.');

        $character = $this->character->getCharacter();

        $character->inventory->slots->each(function($slot) {
            $this->assertFalse($slot->equipped);
        });
    }

    public function testCompareItems() {
        $item = $this->createItem([
            'name'                => 'Sample Item',
            'type'                => 'weapon',
            'base_damage'         => 200,
            'cost'                => 100,
            'crafting_type'       => 'weapon',
            'description'         => 'sample',
            'can_resurrect'       => false,
            'resurrection_chance' => 0.0
        ]);

        $slotId = $this->character->inventoryManagement()->giveItem($item)->getSlotId(0);
        $user   = $this->character->getUser();

        $this->actingAs($user)
            ->visitRoute('game.character.sheet')
            ->visitRoute('game.inventory.compare', [
                'item_to_equip_type' => 'weapon',
                'slot_id'            => $slotId,
                'character'          => $this->character->getCharacter()->id
            ])->see('Equipped');
    }

    public function testSeeCompareItemsWithNoCache() {
        $user = $this->character->inventoryManagement()
            ->giveitem($this->createItem([
                'name' => 'Spear',
                'base_damage' => 6,
                'type' => 'weapon',
                'crafting_type' => 'weapon',
            ]))
            ->getCharacterFactory()
            ->getUser();

        $this->actingAs($user)->visitRoute('game.character.sheet')->visitRoute('game.inventory.compare-items', [
            'user' => $user,
            'slot' => 10,
        ])->see('Item comparison expired.');
    }

    public function testYouAreNotAllowedToDoThatMissingSlot() {
        $user = $this->character->inventoryManagement()
            ->giveitem($this->createItem([
                'name' => 'Spear',
                'base_damage' => 6,
                'type' => 'weapon',
                'crafting_type' => 'weapon',
            ]))
            ->getCharacterFactory()
            ->getUser();

        $this->actingAs($user)->visitRoute('game.character.sheet')->visitRoute('game.inventory.compare-items', [
            'user' => $user,
        ])->see('You are not allowed to do that.');
    }

    public function testCannotSeeComparePage() {
        $item = $this->createItem([
            'name'             => 'Armour',
            'base_damage'      => 6,
            'base_ac'          => 6,
            'type'             => 'gloves',
            'default_position' => 'hands',
            'crafting_type'    => 'armour',
        ]);

        $user = $this->character->inventoryManagement()
            ->giveitem($item)
            ->getCharacterFactory()
            ->getUser();

        $this->actingAs($user)->visitRoute('game.character.sheet')->visitRoute('game.inventory.compare', [
            'item_to_equip_type' => 'apple-sauce',
            'slot_id'            => InventorySlot::where('item_id', $item->id)->first()->id,
            'character'          => $this->character->getCharacter()->id
        ])->see('Error. Invalid Input.');
    }

    public function testSeeComparePageWithNothingEquipped() {

        $user = $this->character->inventoryManagement()
            ->unequipAll()
            ->getCharacterFactory()
            ->getUser();

        $this->actingAs($user)->visitRoute('game.inventory.compare', [
            'item_to_equip_type' => 'weapon',
            'slot_id'            => InventorySlot::first()->id,
            'character'          => $this->character->getCharacter()->id
        ])->see('Equipped')->see('You have nothing equipped for this item type. Anything is better then nothing.');
    }

    public function testCannotSeeComparePageWithItemNotInYourInventory() {
        $user = $this->character->getUser();

        $this->actingAs($user)->visitRoute('game.character.sheet')->visitRoute('game.inventory.compare', [
            'item_to_equip_type' => 'weapon',
            'slot_id'            => '10',
            'character'          => $this->character->getCharacter()->id
        ])->see('Item not found in your inventory.');
    }
}
