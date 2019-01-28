@extends('layouts.clean')

@section('title', 'Create a category')

@section('content')
	<h1>Create a category</h1>
	@if ($errors->any())
	    <div class="alert alert-danger">
	        <ul>
	            @foreach ($errors->all() as $error)
	                <li>{{ $error }}</li>
	            @endforeach
	        </ul>
	    </div>
	@endif
	<form action="/categories" method="POST" accept-charset="utf-8" enctype="multipart/form-data">
	  @csrf
	  <div class="form-group">
		<label for="exampleFormControlInput1">Name</label>
		<input type="text" class="form-control" id="exampleFormControlInput1" name="name" placeholder="Title" required>
	  </div>

	  <div class="form-group">
	    <label for="exampleFormControlFile1">Icon (please proccess with <a href="https://jpeg.io" target="_BLANK">jpeg.io</a>)</label>
	    <input type="file" class="form-control-file" name="icon" id="exampleFormControlFile1" required>
	  </div>
	  <button type="submit" class="btn btn-primary">Publish</button>
	  <hr>
	</form>

@endsection
