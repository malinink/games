<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;


class UserIngameInfoTest extends TestCase
{   
    protected $userId1;
    protected $gameTypesId1;
    protected $userId2;
    protected $gameTypesId2;
    protected $inGameInfo1;
    protected $inGameInfo2;
    protected $user1;
    protected $user2;
    protected $Gametype1;
    protected $Gametype2;
    /**
     * Create new data
     *
     *  @return void
     */
    public function testCreate()
    {
        global $userId1, $gameTypesId1,$userId2,$gameTypesId2,$inGameInfo1,$inGameInfo2,$user1,$user2,$Gametype1,$Gametype2;
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $Gametype1=\App\GameType::create([
            'type_name' => 'normal',
            'time_on_turn'=> 5,
            'is_rating'=>1
        ]);
        $Gametype1->save();
        
        $Gametype2=\App\GameType::create([
            'type_name' => 'rapid',
            'time_on_turn'=> 1,
            'is_rating'=>0
        ]);
        $Gametype2->save();
        
        $user1=\App\User::create([
            'name'=> 'Danmark',
            'email'=>'helloy@mail.ru',
            'password'=>'1234567'
        ]);
        $user1->save();
        $user2=\App\User::create([
            'name'=> 'Norway',
            'email'=>'bye@mail.ru',
            'password'=>'1234567'
        ]);
        $user2->save();
        
        $userId1=$user1->id;
        $userId2=$user2->id;
        $gameTypesId1=$Gametype1->id;
        $gameTypesId2=$Gametype2->id;
        
        $inGameInfo1= \App\UserIngameInfo::create([
            'game_rating'=>1000,
            'games'=>11,
            'wins'=>9
        ])->user()->associate($userId1)->gameType()->associate($gameTypesId1);
        
        $inGameInfo1->save();
        
        $inGameInfo2= \App\UserIngameInfo::create([
            'game_rating'=>1000,
            'games'=>13,
            'wins'=>12
        ])->user()->associate($userId2)->gameType()->associate($gameTypesId2);
        
        $inGameInfo2->save();
        
         
    }
    
    /**
     * Check connection
     *
     *  @return void
     */
    public function testCheckfirst()
    {
        global $gameTypesId1,$userId1;
        $this->seeInDatabase('user_ingame_info',['game_type_id'=> $gameTypesId1,'user_id'=> $userId1]);
    }
    
    /**
     * Check connection
     *
     *  @return void
     */  
    public function testChecksecond()
    {
        global $gameTypesId2,$userId2;
        $this->seeInDatabase('user_ingame_info',['game_type_id'=> $gameTypesId2,'user_id'=> $userId2]);
    }
     
    /**
     * Delete data
     *
     *  @return void
     */ 
    
    public function testRemove()
    {
       global $userId1,$userId2,$inGameInfo1,$inGameInfo2,$Gametype1,$Gametype2;
      \App\UserIngameInfo::find($inGameInfo1->id)->delete();
      \App\UserIngameInfo::find($inGameInfo2->id)->delete();
      \App\GameType::find($Gametype1->id)->delete();
      \App\GameType::find($Gametype2->id)->delete();
      \App\User::find($userId1)->delete();
      \App\User::find($userId2)->delete(); 
    }
}
