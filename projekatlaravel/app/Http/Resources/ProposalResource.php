<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProposalResource extends JsonResource
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
            'amount'        => $this->amount,
            'delivery_days' => $this->delivery_days,
            'cover_letter'  => $this->cover_letter,
            'status'        => $this->status,
            'project_id'    => $this->project_id,
            'freelancer_id' => $this->freelancer_id,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,

            'project'       => new ProjectResource($this->whenLoaded('project')),
            'freelancer'    => new UserResource($this->whenLoaded('freelancer')),
        ];
    }
}
