<?php

namespace App\Http\Resources;

use App\Models\Categories;
use App\Models\Organisations;
use Illuminate\Http\Resources\Json\JsonResource;

class ProtocolItem extends JsonResource
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
            'description' => $this->description,
            'user' => $this->user_id,
            'protocol' => $this->protocol_id,
            'markedAsClosed' => $this->markedAsClosed,

            //Depricated
            '_id' => $this->id,
            'text' => $this->description,
            'autor' => $this->user_id,
            'close' => $this->markedAsClosed
        ];
        return $res;
    }
}
