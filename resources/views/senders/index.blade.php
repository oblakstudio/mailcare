@extends('layouts.app')

@section('content')
<div class="container">
    <list-emails sender="{{ $email }}"></list-emails>
</div>
@endsection
