<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(User $user)
    {
        $this->authorize('index', [User::class]);

        return UserResource::collection($user->public()->selectOnlyPublicAttributes()->withCount('publicMaps')->paginate());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, User $user)
    {
        $this->authorize('view', $user);

        if (! $request->wantsJson()) {
            return view('users.show', ['user' => $user]);
        }

        $request->validate([
            'with' => 'nullable|array',
            'with.*' => 'nullable|string',
        ]);

        if ($request->input('with')) {
            $load = [];
            if (in_array('maps', $request->input('with'))) {
                $load[] = 'publicMaps';
            }
            if (in_array('contributions', $request->input('with'))) {
                $load[] = 'publicMapsContributedTo';
            }
            $user->load($load);
        }

        if (! $request->user() || $request->user()->id !== $user->id) {
            $user->makeHidden(['email', 'name', 'surname', 'id', 'updated_at', 'is_public']);
        }

        return $user;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $roles = Role::get();

        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validatedData = $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:users,username,'.$user->id, 'min:3', 'alpha_dash'],
            'description' => 'nullable|string|max:191',
            'name' => ['nullable', 'string', 'max:255'],
            'surname' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'is_public' => ['nullable', 'boolean'],
        ]);

        $user->update($validatedData);

        if ($request->user()->can('manage user roles')) {
            $roles = $request->input('roles');
            if (isset($roles)) {
                $user->roles()->sync($roles);
            } else {
                $user->roles()->detach();
            }
        }

        if ($request->wantsJson()) {
            return $user;
        }

        return redirect('/users/'.urlencode($request->input('username')));
    }

    /**
     * Update the currently authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateSelf(Request $request)
    {
        $user = $request->user();

        return $this->update($request, $user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function applyForReporter(Request $request)
    {
        $user = $request->user();

        $this->authorize('update', $user);

        $user->assignRole('reporter');

        $user->revokePermissionTo('apply to report');

        return redirect('/');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $this->authorize('forceDelete', $user);

        $user->delete();

        return response()->json(['success' => true]);
    }
}
