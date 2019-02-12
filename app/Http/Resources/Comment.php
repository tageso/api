<?php

namespace App\Http\Resources;

use App\Models\Categories;
use App\Models\Organisations;
use Illuminate\Http\Resources\Json\JsonResource;

class Comment extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $res = [
            'id' => $this->id,
            'agendaItem' => $this->item_id,
            'text' => $this->text,
            'user' => $this->user_id,

            //Depricated
            '_id' => $this->id,

            'autor' => $this->user_id,
            'accountCallName' => \App\Models\UserProfile::query()->where("user_id", "=", $this->user_id)->first()->username,
            'dateString' => $this->getDate()
        ];
        return $res;
    }
}
