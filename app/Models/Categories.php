<?php

namespace App\Models;

use App\Exceptions\HTTPException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

/**
 * App\Models\Categories
 *
 * @property integer $id
 * @property integer $organisation_id
 * @property integer $user_id
 * @property string $name
 * @property integer $position
 * @property string $status
 */
class Categories extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'user_id', 'organisation_id', 'name', 'position', 'status'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        '',
    ];

    static public function getForOrganisation($organisationId, $pagination = true) {
        $categories = self::query()
            ->where("organisation_id", "=", $organisationId)
            ->where("status", "=", "active")
            ->orderBy("position", "ASC");
        if($pagination) {
            $categories = $categories->paginate(20);
        } else {
            $categories = $categories->get();
        }
        return $categories;
    }

    public function calculateNexFreePosition() {
        if(empty($this->organisation_id)) {
            throw new HTTPException("No Organisation set, but needed to calculate Position");
        }

        $res = self::query()
            ->where("organisation_id", "=", $this->organisation_id)
            ->orderBy("position", "DESC")
            ->first();

        if($res == null) {
            $this->position = 0;
        } else {
            $this->position = $res->position + 1;
        }
    }

    public function validate($data)
    {
        // make a new validator object
        $v = Validator::make($data, $this->rules);

        $v->validate();

        $organisation = Organisations::getById($this->organisation_id);
        if($organisation == null) {
            throw new \Exception("Organisation not found");
        }
    }

}
