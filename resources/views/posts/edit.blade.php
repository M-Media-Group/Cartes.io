@extends('layouts.clean')

@section('title', 'Create a post')

@section('content')
	<h1>Edit a post</h1>
	@if ($errors->any())
	    <div class="alert alert-danger">
	        <ul>
	            @foreach ($errors->all() as $error)
	                <li>{{ $error }}</li>
	            @endforeach
	        </ul>
	    </div>
	@endif
	<form action="/posts/{{$post->id}}" method="POST" accept-charset="utf-8" enctype="multipart/form-data">
	  @csrf
	  @method('PATCH')
	  <div class="form-group">
		<label for="exampleFormControlInput1">Title</label>
		<input type="text" class="form-control" id="exampleFormControlInput1" name="title" placeholder="Title" value="{{$post->title}}" required>
	  </div>
	  <div class="form-group">
		<label for="exampleFormControlTextarea1">Post</label>
		<textarea class="form-control" id="exampleFormControlTextarea1" rows="13" name="body_markdown" placeholder="Markdown" required>{{$post->body_markdown}}</textarea>
	  </div>
	  <div class="form-group">
		<label for="exampleFormControlTextarea2">Blurb</label>
		<textarea class="form-control" id="exampleFormControlTextarea2" rows="2" name="excerpt" required>{{$post->excerpt}}</textarea>
	  </div>
	  <div class="form-group">
		<label for="exampleFormControlSelect1">Category</label>
		<select class="form-control" id="exampleFormControlSelect1" name="category_id" required>
			@foreach($categories as $category)
		  		<option value="{{$category->id}}" @if (in_array($category->id, $post->categories->pluck('id')->toArray())) selected="selected" @endif>{{$category->name}}</option>
		  	@endforeach
		</select>
	  </div>
	  <button type="submit" class="btn btn-primary">Save</button>
	  <hr>
	</form>

@endsection
