<?php

namespace Tests\Unit\Flare\View\Livewire\Admin\Users;

use Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Flare\View\Livewire\Admin\Users\DataTable;
use Tests\TestCase;
use Tests\Traits\CreateUser;
use Tests\Setup\CharacterSetup;

class DataTableTest extends TestCase
{
    // use RefreshDatabase, CreateUser;

    // public function setUp(): void {
    //     parent::setUp();

    //     (new CharacterSetup)->setupCharacter($this->createUser(), ['name' => 'trox'])
    //         ->levelCharacterUp(10)
    //         ->setSkill('Accuracy', [
    //             'skill_bonus' => 10,
    //             'xp_towards' => 10,
    //             'currently_training' => true
    //         ])
    //         ->setSkill('Dodge', [
    //             'skill_bonus' => 10,
    //         ])
    //         ->setSkill('Looting', [
    //             'skill_bonus' => 0,
    //         ])
    //         ->getCharacter();

    //     (new CharacterSetup)->setupCharacter($this->createUser(), ['name' => 'Zex'])
    //         ->levelCharacterUp(10)
    //         ->setSkill('Accuracy', [
    //             'skill_bonus' => 10,
    //             'xp_towards' => 10,
    //             'currently_training' => true
    //         ])
    //         ->setSkill('Dodge', [
    //             'skill_bonus' => 10,
    //         ])
    //         ->setSkill('Looting', [
    //             'skill_bonus' => 0,
    //         ])
    //         ->getCharacter();
    // }

    // public function testTheComponentLoads() {

    //     Livewire::test(DataTable::class)
    //             ->assertSee('Zex')
    //             ->assertSee('trox');
    // }

    // public function testTheComponentLoadsOppisiteOrder() {

    //     Livewire::test(DataTable::class, [
    //         'sortAsc' => false
    //     ])
    //     ->assertSee('Zex')
    //     ->assertSee('trox');
    // }

    // public function testSortByCharacterName() {

    //     Livewire::test(DataTable::class)
    //     ->assertSee('Zex')
    //     ->assertSee('trox')
    //     ->call('sortBy', 'characters.name');
    // }

    // public function testSortByCharacterNameWithSearch() {

    //     Livewire::test(DataTable::class)
    //     ->assertSee('Zex')
    //     ->assertSee('trox')
    //     ->call('sortBy', 'characters.name')
    //     ->set('search', 'trox')
    //     ->assertDontSee('Zex');
    // }

    // public function testSortByUserOnline() {

    //     Livewire::test(DataTable::class)
    //     ->assertSee('Zex')
    //     ->assertSee('trox')
    //     ->call('sortBy', 'user.currently_online');
    // }

    // public function testSortByUserOnlineInReverse() {

    //     Livewire::test(DataTable::class)
    //     ->assertSee('Zex')
    //     ->assertSee('trox')
    //     ->call('sortBy', 'user.currently_online')
    //     ->call('sortBy', 'user.currently_online');
    // }

    // public function testSearch() {

    //     Livewire::test(DataTable::class)
    //     ->assertSee('Zex')
    //     ->assertSee('trox')
    //     ->set('search', 'trox')
    //     ->assertDontSee('Zex');
    // }

    // public function testSearchForNonExistantCharacter() {

    //     Livewire::test(DataTable::class)
    //     ->assertSee('Zex')
    //     ->assertSee('trox')
    //     ->set('search', '9879879879')
    //     ->assertDontSee('Zex')
    //     ->assertDontSee('tox');
    // }

    // public function testSearchForCharacterNotOnline() {

    //     Livewire::test(DataTable::class)
    //     ->assertSee('Zex')
    //     ->assertSee('trox')
    //     ->set('search', 'no');
    // }
}
