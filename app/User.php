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
    public static function userHasGame(User $user)
    {
        $lastGame = $user->usergames->last()->game;
        if (is_null($lastGame->time_finished)) {
            if (count($lastGame->usergames->all()) > 1) {
                return 2;
            } else {
                return 1;
            }
        } else {
            return 0;
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
