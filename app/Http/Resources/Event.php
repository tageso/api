<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Event extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // {"title":null,"text":null,"link":null,"user":5,"link_item":null,"link_organisation":1,"link_protocol":15,"typ":"newProtocol","timestamp":1538073520,"target_user":15}
        $data = \GuzzleHttp\json_decode($this->payload);
        $res = [
            'date' => strtotime($this->created_at),
            'text' => null,
            'account' => $data->user,
            'agendaItem' => $data->link_item,
            '_id' => $this->id,
            'typ' => $data->typ,
            'agenda' => $data->link_organisation,
            'payload' => [
                'protocolID' => $data->link_protocol,
                'agendaName' => $this->agendaName,
                'protocolDate' => $this->createt_at,
                'comment' => $data->text,
                'itemName' => $this->itemName,
                'autorCallName' => $this->username
            ],
            'targetAccount' => $data->target_user,
            'dateStr' => date("d.m.Y H:i", strtotime($this->created_at))
        ];

        return $res;
    }
}
