<?php

namespace App\Http\Resources;

use App\Models\Categories;
use App\Models\Organisations;
use Illuminate\Http\Resources\Json\JsonResource;

class Protocol extends JsonResource
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
            'start' => $this->start,
            'ende' => $this->ende,
            'user' => $this->user_id,
            'user_close' => $this->user_closed,
            'status' => $this->status,


            //Depricated
            '_id' => $this->id,
            'date' => $this->start,
            'accountCreated' => $this->user_id,
            'accountClosed' => $this->user_closed,
            'done' => ($this->status == "closed") ? true : false,
            'canceld' => ($this->status == "canceled") ? true : false,

        ];
        return $res;
    }
}
