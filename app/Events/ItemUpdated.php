<?php

namespace App\Events;

use App\Models\Item;
use App\Models\Organisations;
use App\Models\User;

class ItemUpdated extends Event
{
    private $item;
    private $changes = [];
    /**
     * Create a new event instance.
     *
     * @param Item $item Item that Changed
     * @param array $changes Changes of the Item
     *
     * @return void
     */
    public function __construct(Item $item, array $changes)
    {
        $this->item = $item;
        $this->changes = $changes;
    }

    public function getItem(): Item
    {
        return $this->item;
    }

    public function getChanges(): array
    {
        return $this->changes;
    }

    public function getObjectId(): int
    {
        return $this->item->id;
    }

    public function getPayload()
    {
        return [
            "changes" => $this->changes
        ];
    }
}
