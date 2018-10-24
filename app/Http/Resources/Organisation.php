<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Organisation extends JsonResource
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
            'public' => (bool)$this->public,
            'status' => $this->status,
            'url' => $this->url,

            //Depricated
            'delete' => ($this->status == "deleted") ? true : false,
            '_id' => $this->id //Depricated
        ];

        if(isset($this->openProtocol)) {
            $res["openProtocol"] = $this->openProtocol;
        }

        return $res;
    }
}