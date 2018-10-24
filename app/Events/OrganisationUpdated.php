<?php

namespace App\Events;

use App\Models\Organisations;
use App\Models\User;

class OrganisationUpdated extends Event
{
    private $organisations;
    private $changes = [];
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Organisations $organisations, array $changes)
    {
        $this->organisations = $organisations;
        $this->changes = $changes;
    }

    public function getOrganisation(): Organisations {
        return $this->organisations;
    }

    public function getChanges(): array {
        return $this->changes;
    }

    public function getObjectId(): int {
        return $this->organisations->id;
    }
}
