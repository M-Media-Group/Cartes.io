@extends('layouts.clean')

@section('title', 'Edit a user')

@section('content')
	<h1>Edit a role: {{$role->name}}</h1>
	@if ($errors->any())
	    <div class="alert alert-danger">
	        <ul>
	            @foreach ($errors->all() as $error)
	                <li>{{ $error }}</li>
	            @endforeach
	        </ul>
	    </div>
	@endif
	<form action="/roles/{{$role->id}}" method="POST" accept-charset="utf-8" enctype="multipart/form-data">
	  @csrf
	  @method('PATCH')
	<div class="form-group">
{{-- 		<label for="exampleFormControlInput2">Name</label>
 --}}		<input type="hidden" class="form-control" id="exampleFormControlInput2" name="name" placeholder="Username" value="{{$role->name}}" required>
	</div>
	<div class='form-group'>
        @foreach ($permissions as $permission)
            <div class="form-check">
			  <input class="form-check-input" type="checkbox" value="{{$permission->id}}" @if (in_array($permission->id, $role->permissions->pluck('id')->toArray())) checked="checked" @endif id="defaultCheck1{{$permission->name}}" name="permissions[]">
			  <label class="form-check-label" for="defaultCheck1{{$permission->name}}">
			    {{$permission->name}}
			  </label>
			</div>
        @endforeach
    </div>
	  <button type="submit" class="btn btn-primary">Save</button>
	  <hr>
	</form>

@endsection
