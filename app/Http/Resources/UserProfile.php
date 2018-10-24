<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserProfile extends JsonResource
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
            'id' => $this->user_id,
            'username' => $this->username,

            //Depricated
            'mail' => null,
            'admin' => null,
            'lastLogin' => null,
            'delete' => null,
            'newMail' => null,
            'callName' => $this->username,
            'developer' => null,
            'systemAccount' => null,
            'disabledMails' => null,
            'disabledMailsToken' => null,
            '_id' => $this->id
        ];
        return $res;
    }
}
