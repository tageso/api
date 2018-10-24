<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

/**
 * App\Models\Item
 *
 * @property integer $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $disabledMailsToken
 * @property string $mailToken
 * @property string status
 */
class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    private $profile = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'name', 'email',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'disabledMailsToken', 'mailToken'
    ];


    public function generateMailToken($length = 25)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        $this->mailToken = $randomString;
    }

    public function generateDisabledMailToken($length = 25)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        $this->disabledMailsToken = $randomString;
    }

    public function getProfile()
    {
        if ($this->profile !== null) {
            return $this->profile;
        }
        $this->profile = UserProfile::query()->where("user_id", "=", $this->id)->first();
        if ($this->profile == null) {
            throw new \Exception("UserProfile not found");
        }
        return $this->profile;
    }
}
