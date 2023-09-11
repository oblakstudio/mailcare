@extends('layouts.app')

@section('content')
<div class="container">
    <list-emails inbox="{{ $email }}"></list-emails>
</div>
@endsection
