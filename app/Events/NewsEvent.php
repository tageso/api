<?php

namespace App\Events;

use App\Models\User;

class NewsEvent extends Event
{
    private $title;
    private $text;
    private $link;
    private $user;
    private $link_item;
    private $link_organisation;
    private $link_protocol;
    private $typ;
    private $timestamp;
    private $target_user;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(int $user_id, int $target_user, $typ, $title = null, $text = null, $link = null)
    {
        $this->title = $title;
        $this->text = $text;
        $this->link = $link;
        $this->user = $user_id;
        $this->typ = $typ;
        $this->timestamp = time();
        $this->target_user = $target_user;
    }

    //Just for Migration
    public function overwriteTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    public function setOrganisation(int $organisation_id)
    {
        $this->link_organisation = $organisation_id;
    }

    public function setItem(int $item_id)
    {
        $this->link_item = $item_id;
    }

    public function setProtocol(int $protocol_id)
    {
        $this->link_protocol = $protocol_id;
    }

    public function setText($text)
    {
        $this->text = $text;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getText()
    {
        return $this->text;
    }

    public function getLink()
    {
        return $this->link;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getLinkOrganisation()
    {
        return $this->link_organisation;
    }

    public function getLinkItem()
    {
        return $this->link_item;
    }

    public function getLinkProtocol()
    {
        return $this->link_protocol;
    }

    public function getHTTPLink()
    {
        // @todo Return Link to agenda or item
        return $this->link;
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }

    public function getPayload()
    {
        return [
            "title" => $this->title,
            "text"  => $this->text,
            "link"  => $this->link,
            "user"  => $this->user,
            "link_item" => $this->link_item,
            "link_organisation" => $this->link_organisation,
            "link_protocol" => $this->link_protocol,
            "typ" => $this->typ,
            "timestamp" => $this->timestamp,
            "target_user" => $this->target_user
        ];
    }

    public function getObjectId()
    {
        return $this->target_user;
    }
}
