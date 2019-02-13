<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Support\Facades\Log;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

/**
 * App\Models\Comments
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $item_id
 * @property string $text
 */

class Comments extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'item_id', 'text'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public function getDate()
    {
        return $this->created_at->format("Y-m-d H:i:s");
    }
}
