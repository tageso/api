<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

/**
 * App\Models\ProtocolItems
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $protocol_id
 * @property integer $item_id
 * @property string $description
 * @property boolean $markedAsClosed
 */

class ProtocolItems extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'user_id', 'protocol_id', 'item_id', 'description', 'markedAsClosed'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public static function getItemForProtocol(int $item_id, int $protocol_id) : ProtocolItems
    {
        $item = self::query()
            ->where("protocol_id", "=", $protocol_id)
            ->where("item_id", "=", $item_id)
            ->first();
        if ($item == null) {
            $item = new ProtocolItems();
            $item->protocol_id = $protocol_id;
            $item->item_id = $item_id;
            $item->user_id = Auth::user()->id;
        }

        return $item;
    }
    public function getDate() {
        $protocol = Protocol::query()->where("id", "=", $this->protocol_id)->first();
        return $protocol->ende;
    }
}
