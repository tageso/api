<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\UserOrganisations
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $organisation_id
 * @property boolean $admin
 * @property boolean $edit
 * @property boolean $access
 * @property boolean $protocol
 * @property boolean $read
 * @property boolean $new
 * @property boolean $notification_protocol
 * @property boolean $comment
 */
class UserOrganisations extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        '',
    ];

    static public function getAccess(int $user_id, int $organisation_id) : UserOrganisations {
        $res = self::query()
            ->where("user_id", "=", $user_id)
            ->where("organisation_id", "=", $organisation_id)
            ->first();
        if($res == null) {
            $organisation = Organisations::getById($organisation_id);
            return self::guestAccess($organisation, $user_id);
        }
        return $res;
    }

    static public function guestAccess(Organisations $organisation, $user_id = null) {
        $res = new UserOrganisations();
        $res->access = false;
        $res->user_id = $user_id;
        $res->organisation_id = $organisation->id;
        $res->admin = false;
        $res->edit = false;
        #$res->write = false;
        $res->new = true;
        $res->protocol = false;
        $res->read = false;
        $res->notification_protocol = false;
        $res->comment = false;
        if($organisation->public == true) {
            $res->read = true;
        }
        return $res;
    }
}
