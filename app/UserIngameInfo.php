<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserIngameInfo extends Model
{
    protected $table ='userIngameInfo'; 
    protected $fillable = [
        'type_id',
        'user_id',
        'game_rating',
        'games',
        'wins'
    ];
    
    public function users()
    {
        return $this->belongsTo('App\User');
    }
    
    public function gameTypes()
    {
        return $this->belongsTo('App\GameType');
    }
    
}
