@extends('layouts.clean')

@section('title', 'Create a category')

@section('content')
	<h1>Edit a category</h1>
	@if ($errors->any())
	    <div class="alert alert-danger">
	        <ul>
	            @foreach ($errors->all() as $error)
	                <li>{{ $error }}</li>
	            @endforeach
	        </ul>
	    </div>
	@endif
	<form action="/categories/{{$category->id}}" method="POST" accept-charset="utf-8" enctype="multipart/form-data">
	  @csrf
	  @method('PATCH')
	  <div class="form-group">
		<label for="exampleFormControlInput1">Name</label>
		<input type="text" class="form-control" id="exampleFormControlInput1" name="name" placeholder="Title" required value="{{$category->name}}">
	  </div>

	  <button type="submit" class="btn btn-primary">Save</button>
	  <hr>
	</form>

@endsection
