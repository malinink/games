<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $table = 'games';
    protected $fillable = array(
        'id',
        'type_id',
        'private',
        'time_started',
        'time_finished',
        'winner',
        'bonus'
    );
    
    public function boardsInfo()
    {
        return $this->hasMany('App\BoardInfo');
    }
    
    public function turnInfo()
    {
        return $this->hasMany('App\TurnInfo');
    }
    
    public function userGames()
    {
        return $this->hasMany('App\UserGame');
    }
    
    public function gamesType()
    {
        return $this->belongsTo('App\GameType');
    }
}
