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
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'email'       => $this->email,
            'role'        => $this->role,
            'affiliation' => $this->affiliation,
            'orcid'       => $this->orcid,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
             'avatar_url'           => $this->avatar_url,           // gravatar
            'avatar_fallback_url'  => $this->avatar_fallback_url,  // dicebear
            'profile'     => new ProfileResource($this->whenLoaded('profile')),
            'skills'      => SkillResource::collection($this->whenLoaded('skills')),
        ];
    }
}
