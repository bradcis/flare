@extends('layouts.app')

@push('head')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" />
    <script src="https://cdn.jsdelivr.net/gh/mcstudios/glightbox/dist/js/glightbox.min.js"></script>
@endpush

@section('content')
    <div class="container mx-auto px-4 pb-10 mb-5">
        <div class="text-center mb-10 lg:mt-10">
            <h1 class="mb-5 font-thin text-7xl dark:text-gray-300 text-gray-800 text-4xl md:text-9xl">Features</h1>
            <p class="mb-10 dark:text-gray-300 text-gray-800 italic">All the features, none of the cost!</p>
        </div>

        <div>
            <img src="{{asset('promotion/character-sheet.png')}}" class="shadow rounded max-w-full h-auto align-middle border-none img-fluid lg:max-w-[60%] my-4 m-auto glightbox cursor-pointer"/>
            <div class="text-sm text-center">
                Click to make larger.
            </div>
        </div>

        <div class="text-center w-full lg:w-2/4 mx-auto mt-20">
            <h2 class="mb-5 font-thin text-2xl lg:text-5xl dark:text-gray-300 text-gray-800">
                <i class="ra ra-monster-skull"></i>
                Character Development
            </h2>
            <p class="mb-10 dark:text-gray-300 text-gray-800">
                These features help you to develop your character in the world of Tlessa.
            </p>
        </div>

        <div class="grid md:grid-cols-3 gap-3 w-full md:w-2/3 m-auto">
            <x-core.cards.feature-card>
                <x-slot:icon>
                    <i class="ra ra-player text-primary-600 relative top-[10px] right-[10px]"></i>
                </x-slot:icon>
                <x-slot:title>
                    <a href="{{route('info.page', [
                                'pageName' => 'races-and-classes'
                            ])}}">Various Races and Clases!</a>
                </x-slot:title>

                <p>
                    Choose a race for your character and choose a starting class! Races and classes when paired together
                    can give good bonuses towards stats!
                </p>
            </x-core.cards.feature-card>
            <x-core.cards.feature-card>
                <x-slot:icon>
                    <i class="ra ra-double-team text-primary-600 relative top-[10px] right-[10px]"></i>
                </x-slot:icon>
                <x-slot:title>
                    <a href="{{route('info.page', [
                                'pageName' => 'class-ranks'
                            ])}}">Switch Classes and Learn Special Abilities!</a>
                </x-slot:title>

                <p>
                    With class ranks, you can level other classes, learn their special abilities and mix and match. Some classes can
                    only be unlocked through the Class Rank system!
                </p>
            </x-core.cards.feature-card>
            <x-core.cards.feature-card>
                <x-slot:icon>
                    <i class="ra ra-trail text-primary-600 relative top-[10px] right-[10px]"></i>
                </x-slot:icon>
                <x-slot:title>
                    <a href="{{route('info.page', [
                        'pageName' => 'reincarnation',
                    ])}}">Reincarnation</a>
                </x-slot:title>

                <p>
                   Reincarnate your character to set their level back to one, but keep all the skills and stats. Make your self powerful
                   as you re-level and gain more stats! Reincarnate multipletimes to gain more and more power!
                </p>
            </x-core.cards.feature-card>
            <x-core.cards.feature-card>
                <x-slot:icon>
                    <i class="ra ra-player-pyromaniac text-primary-600 relative top-[10px] right-[10px]"></i>
                </x-slot:icon>
                <x-slot:title>
                    <a href="{{route('info.page', [
                        'pageName' => 'skill-information',
                    ])}}">Level Skills</a>
                </x-slot:title>

                <p>
                   Level your character skills to get better at Attacking, Dodgeing, and even unleash special attacks on monsters!
                </p>
            </x-core.cards.feature-card>
            <x-core.cards.feature-card>
                <x-slot:icon>
                    <i class="ra ra-aware text-primary-600 relative top-[10px] right-[10px]"></i>
                </x-slot:icon>
                <x-slot:title>
                    <a href="{{route('info.page', [
                        'pageName' => 'class-skills',
                    ])}}">Class Skills</a>
                </x-slot:title>

                <p>
                   Every class in the game has a special skill, which when leveled will unleash a special attack.
                </p>
            </x-core.cards.feature-card>
            <x-core.cards.feature-card>
                <x-slot:icon>
                    <i class="ra ra-axe text-primary-600 relative top-[10px] right-[10px]"></i>
                </x-slot:icon>
                <x-slot:title>
                    <a href="{{route('info.page', [
                        'pageName' => 'equipment',
                    ])}}">Equipment</a>
                </x-slot:title>

                <p>
                   There is avariety if weapons, armours, rings, spells and so on that you can craft, buy, earn and find through
                   various events, battling, raiding and questing! Outfit your character today!
                </p>
            </x-core.cards.feature-card>
        </div>
        <div class="my-4">
            <div class="mt-10">
                <img src="{{asset('promotion/quests-map.png')}}" class="shadow rounded max-w-full h-auto align-middle border-none img-fluid lg:max-w-[60%] my-4 m-auto glightbox cursor-pointer"/>
                <div class="text-sm text-center">
                    Click to make larger.
                </div>
            </div>

            <div class="text-center w-full lg:w-2/4 mx-auto mt-20">
                <h2 class="mb-5 font-thin text-2xl lg:text-5xl dark:text-gray-300 text-gray-800">
                    <i class="ra ra-footprint"></i>
                    World of Exploration
                </h2>
                <p class="mb-10 dark:text-gray-300 text-gray-800">
                    These are the features that help you explore the world
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-3 w-full md:w-2/3 m-auto">
                <x-core.cards.feature-card>
                    <x-slot:icon>
                        <i class="fas fa-sign text-primary-600 relative top-[10px] right-[10px]"></i>
                    </x-slot:icon>
                    <x-slot:title>
                        <a href="{{route('info.page', [
                                    'pageName' => 'quests'
                                ])}}">Quests</a>
                    </x-slot:title>
    
                    <p>
                        Quests allow you to progress your character futrther and unlock features gated behind the quest system.
                        You can also use quests to unlock the various planes!
                    </p>
                </x-core.cards.feature-card>
                <x-core.cards.feature-card>
                    <x-slot:icon>
                        <i class="ra ra-player-lift text-primary-600 relative top-[10px] right-[10px]"></i>
                    </x-slot:icon>
                    <x-slot:title>
                        <a href="{{route('info.page', [
                                    'pageName' => 'planes'
                                ])}}">Planes</a>
                    </x-slot:title>
    
                    <p>
                        Traverse from the Surface world to the various other planes and fight new and fearsome monsters! Advance your character 
                        and the story with the various quests on each plane
                    </p>
                </x-core.cards.feature-card>
                <x-core.cards.feature-card>
                    <x-slot:icon>
                        <i class="fas fa-dungeon text-primary-600 relative top-[10px] right-[10px]"></i>
                    </x-slot:icon>
                    <x-slot:title>
                        <a href="{{route('info.page', [
                                    'pageName' => 'races-and-classes'
                                ])}}">Locations</a>
                    </x-slot:title>
    
                    <p>
                        Visit tons of locations for quest items, fight harder monsters for specific quest items and drops!
                    </p>
                </x-core.cards.feature-card>
                <x-core.cards.feature-card>
                    <x-slot:icon>
                        <i class="ra ra-batwings text-primary-600 relative top-[10px] right-[10px]"></i>
                    </x-slot:icon>
                    <x-slot:title>
                        <a href="{{route('info.page', [
                                    'pageName' => 'monsters'
                                ])}}">Monsters</a>
                    </x-slot:title>
    
                    <p>
                        Fight monsters to find magical items, gain exp and currencies! Some locations have harder monsters, some planes
                        while weakening you will buff the monster.
                    </p>
                </x-core.cards.feature-card>
                <x-core.cards.feature-card>
                    <x-slot:icon>
                        <i class="ra ra-desert-skull text-primary-600 relative top-[10px] right-[10px]"></i>
                    </x-slot:icon>
                    <x-slot:title>
                        <a href="{{route('info.page', [
                                    'pageName' => 'celestials'
                                ])}}">Celestials</a>
                    </x-slot:title>
    
                    <p>
                        Monsters stronger then the ones that roam the land! You can conjure them and they have a specific 
                        times when they spawn more easily for players to hunt for valuable shards!
                    </p>
                </x-core.cards.feature-card>
                <x-core.cards.feature-card>
                    <x-slot:icon>
                        <i class="ra ra-death-skull text-primary-600 relative top-[10px] right-[10px]"></i>
                    </x-slot:icon>
                    <x-slot:title>
                        <a href="{{route('info.page', [
                                    'pageName' => 'raids'
                                ])}}">Raids</a>
                    </x-slot:title>
    
                    <p>
                        Specific events will corrupt locations on one plane causing a new list of super strong creatures and a special
                        one for all players to try and take down together: Raid Bosses! Players gain epic loot and new Gear 
                        peices you cant find anywhere!
                    </p>
                </x-core.cards.feature-card>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const lightbox = GLightbox();
    </script>
@endpush