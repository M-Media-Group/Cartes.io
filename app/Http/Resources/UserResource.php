<?php

namespace App\Http\Resources;

use App\Models\MapUser;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $userBelongsToAuthenticatedUser = optional($request->user())->can('update', $this);
        return [
            'username' => $this->username,
            'description' => $this->description,
            'avatar' => $this->avatar,
            'email' => $this->when($userBelongsToAuthenticatedUser, $this->email),
            'name' => $this->when($userBelongsToAuthenticatedUser, $this->name),
            'surname' => $this->when($userBelongsToAuthenticatedUser, $this->surname),
            'is_public' => $this->is_public,
            'created_at' => $this->created_at,
            'updated_at' => $this->when($userBelongsToAuthenticatedUser, $this->updated_at),
            'email_verified_at' => $this->email_verified_at,
            // Sometimes we also load the pivot MapUser. If thats the case, we need to also show the can_create_markers
            'can_create_markers' => $this->whenPivotLoaded(new MapUser(), fn () => $this->pivot->can_create_markers),
        ];
    }
}
