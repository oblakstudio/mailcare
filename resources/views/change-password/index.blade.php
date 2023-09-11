@extends('layouts.app')

@section('content')
<div class="container">
    <form method="POST" action="/change-password">
    	@csrf

    	@if ($errors->any())
		    <div class="notification is-danger">
		        <ul>
		            @foreach ($errors->all() as $error)
		                <li>{{ $error }}</li>
		            @endforeach
		        </ul>
		    </div>
		@endif
		
    	<div class="field">
    		<label class="label">Current password</label>
    		<div class="control">
    			<input 
	    			class="input" 
	    			type="password" 
	    			placeholder="old password" 
	    			autocomplete="current-password"
	    			name="current_password"
	    		>
    		</div>
    	</div>
    	<div class="field">
    		<label class="label">New password</label>
    		<div class="control">
    			<input 
    				class="input" 
    				type="password" 
    				placeholder="new password" 
    				autocomplete="new-password"
    				name="password"
    			>
    		</div>
    	</div>
    	<div class="field">
    		<label class="label">Confirm password</label>
    		<div class="control">
    			<input 
    				class="input" 
    				type="password" 
    				placeholder="confirm password" 
    				autocomplete="new-password"
    				name="password_confirmation"
    			>
    		</div>
    	</div>
    	<div class="field">
    		<div class="control">
    			<button class="button is-link">Update password</button>
    		</div>
    	</div>
    </form>
</div>
@endsection
