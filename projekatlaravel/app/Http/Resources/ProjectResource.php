<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
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
            'title'       => $this->title,
            'description' => $this->description,
            'budget'      => $this->budget,
            'status'      => $this->status,
            'client_id'   => $this->client_id,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,

            'client'      => new UserResource($this->whenLoaded('client')),
            'skills'      => SkillResource::collection($this->whenLoaded('skills')),
            'contract'    => new ContractResource($this->whenLoaded('contract')),
        ];
    }
}
