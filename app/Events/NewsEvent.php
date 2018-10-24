<?php

namespace App\Events;

use App\Models\User;

class NewsEvent extends Event
{
    private $title;
    private $text;
    private $link;
    private $user;
    private $timestamp;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user, $title, $text = null, $link = null)
    {
        $this->title = $title;
        $this->text = $text;
        $this->link = $link;
        $this->user = $user;
        $this->timestamp = time();

    }

    public function getTitle() {
        return $this->title;
    }

    public function getText() {
        return $this->text;
    }

    public function getLink() {
        return $this->link;
    }

    public function getUser() {
        return $this->user;
    }

    public function getTimestamp() {
        return $this->timestamp;
    }
}
