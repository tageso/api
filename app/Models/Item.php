<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Support\Facades\Log;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

/**
 * App\Models\Item
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $category_id
 * @property string $status
 * @property string $name
 * @property string $description
 * @property integer $position
 */

class Item extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'category_id', 'status', 'name', 'description', 'position'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public static function getAllForOrganisation($organisation_id)
    {
        $categories = Categories::query()->where("organisation_id", "=", $organisation_id)->get(["id"]);
        $catIds = [];

        foreach ($categories as $category) {
            $catIds[] = $category->id;
        }

        $items = self::query()
            ->whereIn("category_id", $catIds)
            ->get();

        return $items;
    }

    public function calculateNexFreePosition()
    {
        if (empty($this->category_id)) {
            throw new HTTPException("No Organisation set, but needed to calculate Position");
        }

        $res = self::query()
            ->where("category_id", "=", $this->category_id)
            ->orderBy("position", "DESC")
            ->first();

        if ($res == null) {
            $this->position = 0;
        } else {
            $this->position = $res->position + 1;
        }
        return $this->position;
    }

    public static function getForDate($id, $date)
    {
        $object = self::query()->where("id", "=", $id)->first();
        $events = Event::query()
            ->where("eventType", "=", "App\Events\ItemUpdated")
            ->where("eventObjectId", "=", $id)
            ->where("created_at", ">", date("Y-m-d H:i:s e", strtotime($date)))
            ->get();

        Log::debug("Found ".count($events)." Events for Item");

        foreach ($events as $event) {
            $changes = \GuzzleHttp\json_decode($event->payload);
            foreach ($changes->changes as $key => $value) {
                $object->$key = $value->old;
            }
        }

        return $object;
    }
}
