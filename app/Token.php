<?php

/*
 *
 * @author learp
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    /**
     * Table Name
     *
     * @var string
     */
    protected $table = "tokens";
    
    const LIFETIME = 30;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "token",
        "user_id",
        "expiration_date"
    ];
    
    /**
     *
     * @return User
     */
    public function user()
    {
        return $this->belongsTo("App\User");
    }
}
