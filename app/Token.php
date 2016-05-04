<?php
/**
 *
 * @author learp
 */

namespace App;

use DB;
use Carbon\Carbon;
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
     * Disable autoincrementing
     *
     * @var boolean
     */
    public $incrementing = false;
    
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
    
    /**
     * Create and retrun identification token
     *
     * @param \App\User $user
     * @return Token
     */
    public static function getIdentificationToken(User $user)
    {
        $tokenString = "";
        do {
            $tokenString = str_random(100);
        } while (DB::table("tokens")->where("token", "=", $tokenString)->count());
        $user_id = $user->id;
        
        $token = Token::create([
            "token" => $tokenString,
            "user_id" => $user_id,
            "expiration_date" => Carbon::now()->addSecond(self::LIFETIME),
        ]);
        
        return $token;
    }
}
