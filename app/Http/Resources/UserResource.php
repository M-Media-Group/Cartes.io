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
        // return parent::toArray($request);
        $userBelongsToAuthenticatedUser = $request->user() && $request->user()->id === $this->id;
        return [
            'username' => $this->username,
            'description' => $this->description,
            'avatar' => $this->avatar,
            'email' => $this->when($userBelongsToAuthenticatedUser, $this->email),
            'is_public' => $this->is_public,
            'created_at' => $this->created_at,
            'updated_at' => $this->when($userBelongsToAuthenticatedUser, $this->updated_at),
            'email_verified_at' => $this->email_verified_at,
        ];
    }
}
