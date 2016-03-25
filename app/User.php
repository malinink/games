<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'registration_date',
        'last_visit',
        'default_game_type_id',
        'default_game_private',
        'isAdmin',
    ];
    
    /**
     * Attributes to be casted.
     *
     * @var array
     */
    protected $casts = [
        'isAdmin' => 'boolean'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    
    /**
     * Show if user $id has an active game:
     * 0 - no game; 1 - searching for opponent; 2 - playing match
     *
     * @param int $id
     * @return int
     */
    public static function getCurrentGameStatus(User $user)
    {
        constant($NO_GAME) = 0;
        constant($SEARCH_GAME) = 1;
        constant($LIVE_GAME) = 2;
        
        $lastUserGame = $user->usergames->last();
        if (is_null($lastUserGame)) {
                return $NO_GAME;
        }
        $game = $lastUserGame->game;
        if (is_null($game->time_finished)) {
            if (count($game->usergames->all()) > 1) {
                return $LIVE_GAME;
            } else {
                return $SEARCH_GAME;
            }
        } else {
            return $NO_GAME;
        }
    }
    
    /**
     *
     * @return UserIngameInfo[]
     */
    public function useringameinfos()
    {
        return $this->hasMany('App\UserIngameInfo');
    }
    
    /**
     *
     * @return UserGame[]
     */
    public function usergames()
    {
        return $this->hasMany('App\UserGame');
    }
    
    /**
     *
     * @return GameType[]
     */
    public function gametypes()
    {
        return $this->belongsTo('App\GameType');
    }
    
    /**
     *
     * @return User[]
     */
    public function friends()
    {
        return $this->belongsToMany('App\User', 'friends_users', 'friend_id', 'user_id');
    }
}
