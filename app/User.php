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
        'name', 'email', 'password', 'registration_date', 'last_visit', 'default_game_type_id', 'default_game_private'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    
    public function userdefault()
    {
        return $this->hasOne('App\UserDefault');
    }
    
    public function useringameinfos()
    {
        return $this->hasMany('App\UserIngameInfo');
    }
    
    public function usergames()
    {
        return $this->hasMany('App\UserGame');
    }
    
    public function gametypes()
    {
        return $this->belongsTo('App\GameType');
    }
    
    public function friends()
    {
        return $this->belongsToMany('App\User', 'friends_users', 'friend_id', 'user_id');
    }
}
