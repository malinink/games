<?php
/**
 *
 * @IrenJones
 */
namespace App;

use Illuminate\Database\Eloquent\Model;

class UserIngameInfo extends Model
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table ='user_ingame_info';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type_id',
        'user_id',
        'game_rating',
        'games',
        'wins'
    ];
    
    /**
     *
     * @return User[]
     */
    public function users()
    {
        return $this->belongsTo('App\User');
    }
    
    public function gameTypes()
    {
        return $this->belongsTo('App\GameType');
    }
}
