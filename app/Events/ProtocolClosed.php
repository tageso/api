<?php

namespace App\Events;

use App\Models\Organisations;
use App\Models\Protocol;
use App\Models\User;
use phpDocumentor\Reflection\Types\Integer;

class ProtocolClosed extends Event
{
    private $protocol;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Protocol $protocol)
    {
        $this->protocol = $protocol;
    }

    public function getProtocol(): Protocol
    {
        return $this->protocol;
    }

    public function getObjectId(): int
    {
        return $this->protocol->id;
    }

    public function getPayload()
    {
        return [
            "protocol" => $this->protocol
        ];
    }
}
