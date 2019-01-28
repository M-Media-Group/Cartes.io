@extends('layouts.clean')

@section('title', 'Create a post')

@section('content')
	<h1>Create a role</h1>
	@if ($errors->any())
	    <div class="alert alert-danger">
	        <ul>
	            @foreach ($errors->all() as $error)
	                <li>{{ $error }}</li>
	            @endforeach
	        </ul>
	    </div>
	@endif
	<form action="/roles" method="POST" accept-charset="utf-8" enctype="multipart/form-data">
	  @csrf
	<div class="form-group">
		<label for="exampleFormControlInput2">Name</label>
		<input type="text" class="form-control" id="exampleFormControlInput2" name="name" placeholder="Name" value="" required>
	</div>
	<div class='form-group'>
        @foreach ($permissions as $permission)
            <div class="form-check">
			  <input class="form-check-input" type="checkbox" value="{{$permission->id}}" id="defaultCheck1{{$permission->name}}" name="permissions[]">
			  <label class="form-check-label" for="defaultCheck1{{$permission->name}}">
			    {{$permission->name}}
			  </label>
			</div>
        @endforeach
    </div>
	  <button type="submit" class="btn btn-primary">Publish</button>
	  <hr>
	</form>

@endsection
