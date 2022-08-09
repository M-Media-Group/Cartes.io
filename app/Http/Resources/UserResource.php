<?php

namespace App\Http\Resources;

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
        ];
    }
}
