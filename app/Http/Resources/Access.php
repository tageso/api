<?php

namespace App\Http\Resources;

use App\Models\Categories;
use App\Models\Organisations;
use Illuminate\Http\Resources\Json\JsonResource;

class Access extends JsonResource
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
            'account' => $this->user_id,
            'organisation' => $this->organisation_id,
            'admin' => (boolean)$this->admin,
            "edit" => (boolean)$this->edit,
            "new" => (boolean)$this->new,
            "protocol" => (boolean)$this->protocol,
            "read" => (boolean)$this->read,
            "access" => (boolean)$this->access,
            "notificationMailProtocol" => (boolean)$this->notification_protocol,
            "comment" => (boolean)$this->comment,

            //Depricated
            '_id' => $this->id,
            'agenda' => $this->organisation_id,
            'callName' => $this->username
        ];
        return $res;
    }
}