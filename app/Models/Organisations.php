<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Organisations
 *
 * @property integer $id
 * @property string $name
 * @property integer $user_id
 * @property boolean $public
 * @property string $url
 * @property string $status
 */
class Organisations extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'user_id', 'public', 'url', 'status'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        '',
    ];

    public static function getById($id)
    {
        $res = self::query()
            ->where("id", "=", $id)
            ->where("status", "=", "active")
            ->first();
        return $res;
    }
}
