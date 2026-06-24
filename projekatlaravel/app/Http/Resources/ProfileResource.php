<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
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
            'id'           => $this->id,
            'bio'          => $this->bio,
            'github_url'   => $this->github_url,
            'portfolio_url'=> $this->portfolio_url,
            'hourly_rate'  => $this->hourly_rate,
            'location'     => $this->location,
            'user_id'      => $this->user_id,
        ];
    }
}
