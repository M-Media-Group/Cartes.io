@extends('layouts.clean')

@section('title', 'Create a post')

@section('content')
	<h1>Create a post</h1>
	@if ($errors->any())
	    <div class="alert alert-danger">
	        <ul>
	            @foreach ($errors->all() as $error)
	                <li>{{ $error }}</li>
	            @endforeach
	        </ul>
	    </div>
	@endif
	<p>You can <a href="/write">read the guidelines here</a>.</p>
	<form action="/posts" method="POST" accept-charset="utf-8" enctype="multipart/form-data">
	  @csrf
	  <div class="form-group">
		<label for="exampleFormControlInput1">Title</label>
		<input type="text" class="form-control" id="exampleFormControlInput1" name="title" placeholder="Title" required autofocus>
	  </div>
	  <div class="form-group">
		<label for="exampleFormControlTextarea1">Post</label>
		<textarea class="form-control" id="exampleFormControlTextarea1" rows="13" name="body_markdown" placeholder="Use the Markdown format" required></textarea>
	  </div>
	  <div class="form-group">
		<label for="exampleFormControlTextarea2">Blurb</label>
		<textarea class="form-control" id="exampleFormControlTextarea2" rows="2" name="excerpt" required></textarea>
	  </div>
	  <div class="form-group">
		<label for="exampleFormControlSelect1">Category</label>
		<select class="form-control" id="exampleFormControlSelect1" name="category_id" required>
			@foreach($categories as $category)
		  		<option value="{{$category->id}}">{{$category->name}}</option>
		  	@endforeach
		</select>
	  </div>
	  <div class="form-group">
	    <label for="exampleFormControlFile1">Header image (please proccess with <a href="https://jpeg.io" target="_BLANK">jpeg.io</a>)</label>
	    <input type="file" class="form-control-file" name="header_image" id="exampleFormControlFile1" required>
	  </div>
	  <button type="submit" class="btn btn-primary">Publish</button>
	</form>

@endsection
