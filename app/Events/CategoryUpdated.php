<?php

namespace App\Events;

use App\Models\Categories;
use App\Models\Item;
use App\Models\Organisations;
use App\Models\User;

class CategoryUpdated extends Event
{
    private $category;
    private $changes = [];
    /**
     * Create a new event instance.
     *
     * @param Categories $cat Category that Changed
     * @param array $changes Changes of the Item
     *
     * @return void
     */
    public function __construct(Categories $cat, array $changes)
    {
        $this->category = $cat;
        $this->changes = $changes;
    }

    public function getCategory(): Categories
    {
        return $this->category;
    }

    public function getChanges(): array
    {
        return $this->changes;
    }

    public function getObjectId(): int
    {
        return $this->category->id;
    }

    public function getPayload()
    {
        return [
            "changes" => $this->changes
        ];
    }
}
