<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
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
            'email' => $this->email,
            'admin' => $this->admin,
            'status' => $this->status,
            'developer' => $this->developer,
            'systemAccount' => $this->systemAccount,


            #'active','validateSend','disabled'
            //Depricated
            'mail' => $this->email,
            'username' => $this->name,
            'lastLogin' => null,
            'delete' => false,
            'newMail' => null,
            'callName' => $this->callName,
            'disabledMails' => false,
            'disabledMailsToken' => null,
            '_id' => $this->id
        ];

        if ($this->status = "deleted") {
            $res["delete"] = true;
        }
        return $res;
    }
}
