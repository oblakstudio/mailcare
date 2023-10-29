@extends('layouts.app')

@section('content')
<div class="container">
    <div class="columns">
        <div class="column">
            <show-email id="{{ $id }}"></show-email>
        </div>
    </div>
</div>
@endsection
