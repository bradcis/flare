<?php

namespace Tests\Unit\Game\NpcActions\QueenOfHeartsActions\Services;

use App\Flare\Values\ItemEffectsValue;
use App\Game\Messages\Events\GlobalMessageEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use App\Flare\Values\MaxCurrenciesValue;
use App\Game\Core\Events\UpdateTopBarEvent;
use App\Game\Messages\Events\ServerMessageEvent;
use App\Game\NpcActions\QueenOfHeartsActions\Events\UpdateQueenOfHeartsPanel;
use App\Game\NpcActions\QueenOfHeartsActions\Services\QueenOfHeartsService;
use Tests\Setup\Character\CharacterFactory;
use Tests\TestCase;
use Tests\Traits\CreateGameMap;
use Tests\Traits\CreateItem;

class QueenOfHeartsServiceTest extends TestCase
{

    use RefreshDatabase, CreateItem, CreateGameMap;

    private ?CharacterFactory $character;

    private ?QueenOfHeartsService $queenOfHeartsService;

    public function setUp(): void {

        parent::setUp();

        $this->character = (new CharacterFactory())->createBaseCharacter()->givePlayerLocation();
        $this->queenOfHeartsService = resolve(QueenOfHeartsService::class);
    }

    public function tearDown(): void {

        parent::tearDown();

        $this->character = null;
        $this->queenOfHeartsService = null;
    }

    public function testNotInHell() {
        Event::fake();

        $character = $this->character->getCharacter();

        $result = $this->queenOfHeartsService->purchaseUnique($character, 'basic');

        Event::assertDispatched(GlobalMessageEvent::class);

        $this->assertEquals('Invalid location to use that.', $result['message']);
        $this->assertEquals(422, $result['status']);
    }

    public function testCannotPurchaseWhenInventoryIsFull() {
        $questItem = $this->createItem(['effect' => ItemEffectsValue::QUEEN_OF_HEARTS]);

        $character = $this->character->inventoryManagement()->giveItem($questItem)->getCharacter();

        $character->update([
            'inventory_max' => 0,
        ]);

        $gameMap = $this->createGameMap(['name' => 'Hell']);

        $character->map()->update(['game_map_id' => $gameMap->id]);

        $character = $character->refresh();

        $result = $this->queenOfHeartsService->purchaseUnique($character, 'basic');

        $this->assertEquals('Your inventory is full.', $result['message']);
        $this->assertEquals(422, $result['status']);
    }

    public function testCannotPurchaseWhenYouHaveNoGold() {
        $questItem = $this->createItem(['effect' => ItemEffectsValue::QUEEN_OF_HEARTS]);

        $character = $this->character->inventoryManagement()->giveItem($questItem)->getCharacter();

        $character->update([
            'gold' => 0,
        ]);

        $gameMap = $this->createGameMap(['name' => 'Hell']);

        $character->map()->update(['game_map_id' => $gameMap->id]);

        $character = $character->refresh();

        $result = $this->queenOfHeartsService->purchaseUnique($character, 'basic');

        $this->assertEquals('Not enough gold.', $result['message']);
        $this->assertEquals(422, $result['status']);
    }

    public function testPurchaseUnique() {
        Event::fake();

        $questItem = $this->createItem(['effect' => ItemEffectsValue::QUEEN_OF_HEARTS]);

        $character = $this->character->inventoryManagement()->giveItem($questItem)->getCharacter();

        $character->update([
            'gold' => MaxCurrenciesValue::MAX_GOLD,
        ]);

        $gameMap = $this->createGameMap(['name' => 'Hell']);

        $character->map()->update(['game_map_id' => $gameMap->id]);

        $character = $character->refresh();

        $result = $this->queenOfHeartsService->purchaseUnique($character, 'basic');

        Event::assertDispatched(UpdateTopBarEvent::class);
        Event::assertDispatched(UpdateQueenOfHeartsPanel::class);
        Event::assertDispatched(ServerMessageEvent::class);

        $character = $character->refresh();

        $this->assertEquals(200, $result['status']);
        $this->assertEquals(2, $character->inventory->slots->count()); // Quest Item + Unique = 2
        $this->assertLessThan(MaxCurrenciesValue::MAX_GOLD, $character->gold);
    }

    public function testCannotReRollForItemThatDoesntExist() {
        $character = $this->character->getCharacter();

        $result = $this->queenOfHeartsService->reRollUnique($character, 1, 'all-enchantments', 'everything');

        $this->assertEquals('Where did you put that item, child? Ooooh hooo hooo hooo! Are you playing hide and seek with it? (Unique does not exist.)', $result['message']);
        $this->assertEquals(422, $result['status']);
    }

    public function testCannotReRollWhenNotInHell() {
        Event::fake();

        $character = $this->character->inventoryManagement()->giveItem($this->createItem())->getCharacter();

        $slot = $character->inventory->slots()->first();

        $result = $this->queenOfHeartsService->reRollUnique($character, $slot->id, 'all-enchantments', 'everything');

        Event::assertDispatched(GlobalMessageEvent::class);

        $this->assertEquals('Invalid location to use that.', $result['message']);
        $this->assertEquals(422, $result['status']);
    }

    public function testCannotReRollWhenCantAfford() {
        $gameMap   = $this->createGameMap(['name' => 'Hell']);

        $character = $this->character->inventoryManagement()
                                     ->giveItem($this->createItem(['effect' => ItemEffectsValue::QUEEN_OF_HEARTS]))
                                     ->getCharacter();

        $character->map()->update(['game_map_id' => $gameMap->id]);

        $character->update(['gold' => 100000000000]);

        $character = $character->refresh();

        $this->queenOfHeartsService->purchaseUnique($character, 'legendary');

        $character = $character->refresh();

        $slotWithUnique = $character->inventory->slots->filter(function($slot) {
            return $slot->item->is_unique;
        })->first();

        $result = $this->queenOfHeartsService->reRollUnique($character, $slotWithUnique->id, 'all-enchantments', 'everything');

        $this->assertEquals('What! No! Child! I don\'t like poor people. I don\'t even date poor men! Oh this is so saddening, child! (You don\'t have enough currency, you made the Queen sad.)', $result['message']);
        $this->assertEquals(422, $result['status']);
    }

    public function testCanReRoll() {
        Event::fake();

        $gameMap   = $this->createGameMap(['name' => 'Hell']);

        $character = $this->character->inventoryManagement()
                                     ->giveItem($this->createItem(['effect' => ItemEffectsValue::QUEEN_OF_HEARTS]))
                                     ->getCharacter();

        $character->map()->update(['game_map_id' => $gameMap->id]);

        $character->update(['gold' => MaxCurrenciesValue::MAX_GOLD, 'gold_dust' => MaxCurrenciesValue::MAX_GOLD_DUST, 'shards' => MaxCurrenciesValue::MAX_SHARDS]);

        $character = $character->refresh();

        $this->queenOfHeartsService->purchaseUnique($character, 'legendary');

        $character = $character->refresh();

        $slotWithUnique = $character->inventory->slots->filter(function($slot) {
            return $slot->item->is_unique;
        })->first();

        $result = $this->queenOfHeartsService->reRollUnique($character, $slotWithUnique->id, 'everything', 'all-enchantments');

        Event::assertDispatched(ServerMessageEvent::class);

        $this->assertEquals(200, $result['status']);
    }
}
