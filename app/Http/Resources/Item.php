<?php

namespace App\Http\Resources;

use App\Models\Categories;
use App\Models\Organisations;
use Illuminate\Http\Resources\Json\JsonResource;

class Item extends JsonResource
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
            'description' => $this->description,
            "category" => $this->category_id,
            "position" => $this->position,
            "account" => $this->user_id,
            "date" => strtotime($this->created_at),
            "status" => $this->status,

            //Depricated
            '_id' => $this->id,
            "agenda" => Organisations::getById(
                Categories::query()
                    ->where("id", "=", $this->category_id)
                    ->first()
                    ->organisation_id
            )->id
            ,
            "done" => ($this->status == "closed") ? true : false,
        ];
        return $res;
    }
}
