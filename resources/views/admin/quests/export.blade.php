@extends('layouts.app')

@section('content')
    <x-core.layout.info-container>
        <x-core.page-title title="Export Quest Data" route="{{route('home')}}" color="success" link="Home">
        </x-core.page-title>

        <x-core.cards.card>
            <div class="text-center mt-4">
                <div class="clearfix" style="width: 250px; margin: 0 auto;">
                    <form method="POST" action="{{ route('quests.export-data') }}" class="float-left">
                        @csrf
                        <x-core.buttons.primary-button type="submit">Export</x-core.buttons.primary-button>
                    </form>
                </div>
            </div>
        </x-core.cards.card>
    </x-core.layout.info-container>
@endsection
