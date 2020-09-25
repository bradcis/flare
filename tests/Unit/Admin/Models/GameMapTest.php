<?php

namespace Tests\Unit\Admin\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use App\Flare\Models\GameMap;
use Tests\Setup\CharacterSetup;
use Tests\Traits\CreateUser;
use Tests\TestCase;

class GameMapTest extends TestCase
{
    use RefreshDatabase, CreateUser;

    private $character;

    public function setUp(): void {
        parent::setup();

        $this->setUpCharacter();
    }

    public function tearDown(): void {
        parent::tearDown();

        $this->character = null;

        Storage::disk('maps')->deleteDirectory('Surface/');
    }

    public function testGameMapCanGetMaps()
    {
        $this->assertTrue(GameMap::first()->maps->isNotEmpty());
    }

    protected function setUpCharacter(array $options = []) {
        $user = $this->createUser();

        $path = Storage::disk('maps')->putFile('Surface', resource_path('maps/surface.jpg'));

        $gameMap = GameMap::create([
            'name'    => 'surface',
            'path'    => $path,
            'default' => true,
        ]);

        $this->character = (new CharacterSetup)->setupCharacter($user, $options)
                                               ->getCharacter();

        $this->character->map()->create([
            'character_id' => $this->character->id,
            'game_map_id'  => $gameMap->id,
        ]);
    }
}
