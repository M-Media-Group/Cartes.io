@extends('layouts.clean')

@section('title', 'Edit a user')

@section('content')
	<h1>Edit account</h1>
	@if ($errors->any())
	    <div class="alert alert-danger">
	        <ul>
	            @foreach ($errors->all() as $error)
	                <li>{{ $error }}</li>
	            @endforeach
	        </ul>
	    </div>
	@endif
	<form action="/users/{{$user->id}}" method="POST" accept-charset="utf-8" enctype="multipart/form-data">
	  @csrf
	  @method('PATCH')

	  <div class="form-group">
		<label for="exampleFormControlInput1">Username</label>
		<input type="text" class="form-control" id="exampleFormControlInput1" name="username" placeholder="Username" value="{{$user->username}}" required>
	  </div>
	  <div class="form-group">
		<label for="exampleFormControlInput2">Name</label>
		<input type="text" class="form-control" id="exampleFormControlInput2" name="name" placeholder="Username" value="{{$user->name}}" required>
	  </div>
	  	  <div class="form-group">
		<label for="exampleFormControlInput3">Surname</label>
		<input type="text" class="form-control" id="exampleFormControlInput3" name="surname" placeholder="Username" value="{{$user->surname}}" required>
	  </div>
	  <div class="form-group">
		<label for="exampleFormControlInput4">Email</label>
		<input type="text" class="form-control" id="exampleFormControlInput4" name="email" placeholder="Username" value="{{$user->email}}" required>
	  </div>

    @if(Auth::user()->can('manage user roles'))
    	<h1>User roles</h1>
	<div class='form-group'>
        @foreach ($roles as $role)
            <div class="form-check">
			  <input class="form-check-input" type="checkbox" value="{{$role->id}}" @if (in_array($role->name, $user->getRoleNames()->toArray())) checked="checked" @endif id="defaultCheck1{{$role->name}}" name="roles[]">
			  <label class="form-check-label" for="defaultCheck1{{$role->name}}">
			    {{$role->name}}<small><ul>
			    	@foreach ($role->permissions as $permission)
			    	<li>{{$permission->name}}</li>
			    	@endforeach
			    </ul></small>
			  </label>
			</div>
        @endforeach
    </div>
    @endif
	  <button type="submit" class="btn btn-primary">Save</button>
	  <hr>
	</form>

@endsection
