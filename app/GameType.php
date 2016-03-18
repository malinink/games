<?php
/**
 *
 * @Ananaskelly
 */
namespace App;

use Illuminate\Database\Eloquent\Model;

class GameType extends Model
{
    
    protected $table = 'game_types';
    
    protected $fillable = array(
        'id',
        'type_name',
        'time_on_turn',
        'is_rating'
    );
    
    /**
     *
     * @return UserIngameInfo[]
     */
    public function userIngameInfos()
    {
        return $this->hasMany('App\UserIngameInfo');
    }
    
    /**
     *
     * @return Game[]
     */
    public function games()
    {
        return $this->hasMany('App\Game');
    }
    
    /**
     *
     * @return User[]
     */
    public function users()
    {
        return $this->hasMany('App\User');
    }
}
