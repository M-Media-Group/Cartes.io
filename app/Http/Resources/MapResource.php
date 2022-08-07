<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MapResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'privacy' => $this->privacy,
            'users_can_create_markers' => $this->users_can_create_markers,
            'options' => $this->options,
            'uuid' => $this->uuid,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'markers_count' => $this->when($this->markers_count, $this->markers_count),
            'categories' => $this->whenLoaded('categories'),
            'is_linked_to_user' => $this->is_linked_to_user,
            'user' => $this->when($this->user && $this->user->is_public, $this->user),
            // Show the token only if the model was just created
            'token' => $this->when($this->wasRecentlyCreated, $this->token),
            'public_contributors' => $this->whenLoaded('publicContributors'),
            'related' => $this->whenLoaded('related'),
            'markers' => $this->whenLoaded('markers'),
        ];
    }
}
