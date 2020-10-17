@extends('layouts.information', [
    'pageTitle' => $pageName
])

@section('content')
    <div class="mt-5">
        @include('admin.races.race', [
            'race' => $race,
            'customClass' => 'mt-5'
        ])
    </div>
@endsection
