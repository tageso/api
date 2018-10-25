<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

/**
 * App\Models\EmailValidation
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $user_closed
 * @property integer $organisation_id
 * @property string $start
 * @property string $ende
 * @property string $old_uid
 * @property string $status
 */

class Protocol extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'user_closed', 'user_id', 'organisation_id', 'start', 'ende', 'old_uid', 'status'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];
}
