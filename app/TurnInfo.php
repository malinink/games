<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TurnInfo extends Model
{
    protected $table= 'turnInfo';
    protected $fillable = array(
            'game_id',
            'turn_number',
            'move',
            'options',
            'turn_start_time',
            'user_turn'
    );
    
    public function games()
    {
        return $this->belongsTo('App\Game');
    }
    
}
