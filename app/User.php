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
        'is_admin',
    ];
    
    /**
     * Attributes to be casted.
     *
     * @var array
     */
    protected $casts = [
        'is_admin' => 'boolean'
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
     *
     * @return UserIngameInfo[]
     */
    public function userIngameInfos()
    {
        return $this->hasMany('App\UserIngameInfo');
    }
    
    /**
     *
     * @return UserGame[]
     */
    public function userGames()
    {
        return $this->hasMany('App\UserGame');
    }
    
    /**
     *
     * @return GameType[]
     */
    public function gameTypes()
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
