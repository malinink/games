<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BoardInfo extends Model
{
    protected $table = 'boardsInfo';
    protected $fillable = array(
        'game_id',
        'figure',
        'position',
        'color',
        'special',
        'turn_number'
    );
    
    public function games()
    {
        return $this->belongsTo('App\Game');
    }  
}
