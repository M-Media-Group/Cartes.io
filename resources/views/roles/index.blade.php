@extends('layouts.clean')

@section('title', 'Roles')

@section('content')
	<h1 class="mt-3">Roles</h1>
	@foreach($roles as $role)
		<a href="/roles/{{$role->id}}/edit" title="{{ $role->title }}">
		    <h2>{{ $role->name }}</h2>
		    <p class="text-muted">
		    	<ul>
		    	@foreach($role->permissions as $permission)
			       <li class="text-muted">{{$permission->name}}</li>
			    @endforeach
				</ul>
		    </p>
		    <hr>
		</a>
    @endforeach
    {{$roles->links()}}
@endsection
