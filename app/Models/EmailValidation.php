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
 * @property string $email
 * @property string $token
 * @property string $status
 * @property string $used_for
 */

class EmailValidation extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'user_id', 'email', 'token', 'status', 'used_for'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public function generateToken()
    {
        $this->token = str_random(25);
        return $this->token;
    }
}
