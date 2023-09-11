@extends('layouts.app')

@section('content')
<div class="container">
    @if (config('mailcare.forward'))
        <automations forward></automations>
    @else
        <automations></automations>
    @endif
</div>
@endsection
