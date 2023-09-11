@extends('layouts.app')

@push('scripts') 
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.6.0/Chart.min.js"></script> 
@endpush 


@section('content')
<div class="container">
    <statistics></statistics>
</div>
@endsection
