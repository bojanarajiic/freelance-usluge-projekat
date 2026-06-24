<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
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
            'id'         => $this->id,
            'project_id' => $this->project_id,
            'reviewer_id'=> $this->reviewer_id,
            'reviewee_id'=> $this->reviewee_id,
            'rating'     => $this->rating,
            'comment'    => $this->comment,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'project'    => new ProjectResource($this->whenLoaded('project')),
            'reviewer'   => new UserResource($this->whenLoaded('reviewer')),
            'reviewee'   => new UserResource($this->whenLoaded('reviewee')),
        ];
    }
}
