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
            'markers' => $this->whenLoaded('markers'),
            'markers_count' => $this->when(isset($this->markers_count), $this->markers_count),
            'active_markers_count' => $this->when(isset($this->active_markers_count), $this->active_markers_count),
            'categories' => $this->whenLoaded('categories'),
            'categories_count' => $this->when(isset($this->categories_count), $this->categories_count),
            'is_linked_to_user' => $this->is_linked_to_user,
            'user' => $this->whenLoaded(
                'user',
                $this->when(
                    $this->user && ($this->user->is_public || optional($request->user())->can('view', $this->user)),
                    new UserResource($this->user)
                )
            ),
            // Show the token only if the model was just created
            'token' => $this->when($this->wasRecentlyCreated, $this->token),
            'public_contributors' => $this->whenLoaded('publicContributors'),
            'public_contributors_count' => $this->when(isset($this->public_contributors_count), $this->public_contributors_count),
            'related' => $this->whenLoaded('related'),
            'related_count' => $this->when(isset($this->related_count), $this->related_count),
        ];
    }
}
