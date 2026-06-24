<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ContractResource extends JsonResource
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
            'id'            => $this->id,
            'project_id'    => $this->project_id,
            'freelancer_id' => $this->freelancer_id,
            'agreed_amount' => $this->agreed_amount,
            'status'        => $this->status,
            'start_at'      => $this->start_at,
            'end_at'        => $this->end_at,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,

            'project'       => new ProjectResource($this->whenLoaded('project')),
            'freelancer'    => new UserResource($this->whenLoaded('freelancer')),
        ];
    }
}
