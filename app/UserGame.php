<?php
/*
 *
 * @artesby
 */
namespace App;

use Illuminate\Database\Eloquent\Model;

class UserGame extends Model
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table ='user_games';
    
    protected $fillable = [
        'user_id', 'game_id', 'color', 'reserve_time',
    ];
    
    public function user()
    {
        return $this->belongsTo('App\User');
    }
    
    public function game()
    {
        return $this->belongsTo('App\Game');
    }
}
