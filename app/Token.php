<?php
/**
 *
 * @author learp
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    /**
     * in seconds
     *
     * @var const int
     */
    const LIFETIME = 30;
    
    /**
     * Table Name
     *
     * @var string
     */
    protected $table = "tokens";
    
    /**
     * Disable Timestamps fields
     *
     * @var boolean
     */
    public $timestamps = false;
    /**
     * Primary Key
     *
     * @var string
     */
    protected $primaryKey = "token";

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
