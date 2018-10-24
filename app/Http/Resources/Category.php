<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Category extends JsonResource
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
            'name' => $this->name,
            'organisation' => $this->organisation_id,
            'status' => $this->status,
            'position' => $this->position,


            //Depricated
            'openItemsCount' => $this->openItemsCount,
            'agenda' => $this->organisation_id,
            'delete' => ($this->status == "deleted") ? true : false,
            '_id' => $this->id //Depricated
        ];


        if(isset($this->items)) {
            $res["items"] = Item::collection(collect($this->items));
        }


        return $res;
    }
}