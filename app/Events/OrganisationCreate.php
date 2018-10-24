<?php

namespace App\Events;

use App\Models\Organisations;
use App\Models\User;
use phpDocumentor\Reflection\Types\Integer;

class OrganisationCreate extends Event
{
    private $organisations;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Organisations $organisations)
    {
        $this->organisations = $organisations;
    }

    public function getOrganisation(): Organisations {
        return $this->organisations;
    }

    public function getObjectId(): int {
        return $this->organisations->id;
    }

    public function getPayload() {
        return [
            "organisation" => $this->organisations
        ];
    }
}
