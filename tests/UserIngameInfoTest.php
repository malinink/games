<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserIngameInfoTest extends TestCase
{
    protected $userId1;
    protected $gameTypesId1;
    protected $inGameInfo1Id;
    
    protected $userId2;
    protected $gameTypesId2;
    protected $inGameInfo2Id;
    
    protected $Gametype1;
    protected $Gametype2;
    
    protected $user1;
    protected $user2;
    
    protected $inGameInfo1;
    protected $inGameInfo2;
    
    protected $inGameInfo3;
    protected $inGameInfo3Id;
    protected $userId3;
    protected $gameTypesId3;
    /**
     * Create new data
     *
     *  @return void
     */
    public function testCreate()
    {
        DB::statement('ALTER TABLE game_types AUTO_INCREMENT=1;');
        DB::statement('ALTER TABLE user_ingame_infos AUTO_INCREMENT=1;');
        DB::statement('ALTER TABLE users AUTO_INCREMENT=1;');
        
        global $userId1, $gameTypesId1,$userId2,$gameTypesId2,
            $inGameInfo1Id,$inGameInfo2Id,$inGameInfo3Id,
            $gameTypesId3,$userId3;
        
        //With links
        
        $Gametype1 = factory(App\GameType::class)->make();
        $Gametype1->save();
        
        $Gametype2 = factory(App\GameType::class)->make();
        $Gametype2->save();
        
        $user1 = factory(App\User::class)->make();
        $user1->save();
        
        $user2 = factory(App\User::class)->make();
        $user2->save();
        
        $userId1=$user1->id;
        $userId2=$user2->id;
        
        $gameTypesId1=$Gametype1->id;
        $gameTypesId2=$Gametype2->id;
        
        $inGameInfo1= factory(App\UserIngameInfo::class)->make()
            ->user()->associate($userId1)->gameType()->associate($gameTypesId1);
        
        $inGameInfo1->save();
        
        $inGameInfo2= factory(App\UserIngameInfo::class)->make()
            ->user()->associate($userId2)->gameType()->associate($gameTypesId2);
        
        $inGameInfo2->save();
       
        $inGameInfo1Id=$inGameInfo1->id;
        $inGameInfo2Id=$inGameInfo2->id;
        
        //Without links
        
        $gameTypesId3 = factory(App\GameType::class)->create()->id;
        
        $userId3 = factory(App\User::class)->create()->id;
     
        $inGameInfo3= factory(App\UserIngameInfo::class)->make()
            ->user()->associate($userId3)->gameType()->associate($gameTypesId3);
        
        $inGameInfo3->save();
        $inGameInfo3Id=$inGameInfo3->id;
        
    }
    
    /**
     * Check connection
     *
     * @return void
     */
    public function testCheckfirst()
    {
        global $gameTypesId1,$userId1,$inGameInfo1Id;
        
        $this->seeInDatabase(
            'user_ingame_infos',
            ['game_type_id'=> $gameTypesId1,'user_id'=> $userId1]
        );
        
        //testing from one side
        $info=  App\UserIngameInfo::find($inGameInfo1Id);
        $Emil=$info->user;
        $this->assertEquals($info->user_id, $Emil->id);
        
        $chess=$info->gameType;
        $this->assertEquals($info->game_type_id, $chess->id);
        
        //testing from other side
        $Berwald=  App\User::find($userId1);
        $infoBerwald=$Berwald->userIngameInfos()->first();
        $this->assertEquals($Berwald->id, $infoBerwald->user_id);
        
        $gameTino= App\GameType::find($gameTypesId1);
        $infoTino=$gameTino->userIngameInfos()->first();
        $this->assertEquals($gameTino->id, $infoTino->game_type_id);
    }
    /**
     * Check connection
     *
     * @return void
     */
    public function testChecksecond()
    {
        global $gameTypesId2,$userId2,$inGameInfo2Id;
        
        $this->seeInDatabase(
            'user_ingame_infos',
            ['game_type_id'=> $gameTypesId2,'user_id'=> $userId2]
        );
        
        //testing from one side
        $info=  App\UserIngameInfo::find($inGameInfo2Id);
        $Lucas=$info->user;
        $this->assertEquals($info->user_id, $Lucas->id);
        
        $chess=$info->gameType;
        $this->assertEquals($info->game_type_id, $chess->id);
        
        
        //testing from other side
        $Berwald=  App\User::find($userId2);
        $infoBerwald=$Berwald->userIngameInfos()->first();
        $this->assertEquals($Berwald->id, $infoBerwald->user_id);
        
        $gameTino= App\GameType::find($gameTypesId2);
        $infoTino=$gameTino->userIngameInfos()->first();
        $this->assertEquals($gameTino->id, $infoTino->game_type_id);
    }
    /**
     * Check connection
     *
     * @return void
     */
    public function testCheckthird()
    {
        global $gameTypesId3,$userId3,$inGameInfo3Id;
        
        //testing from one side
        $info= App\UserIngameInfo::find($inGameInfo3Id);
        $Lucas=$info->user;
        $this->assertEquals($info->user_id, $Lucas->id);
        
        $chess=$info->gameType;
        $this->assertEquals($info->game_type_id, $chess->id);
        
        //testing from other side
        $Berwald=  App\User::find($userId3);
        $infoBerwald=$Berwald->userIngameInfos()->first();
        $this->assertEquals($Berwald->id, $infoBerwald->user_id);
        
        $gameTino= App\GameType::find($gameTypesId3);
        $infoTino=$gameTino->userIngameInfos()->first();
        $this->assertEquals($gameTino->id, $infoTino->game_type_id);
    
    }
    /**
     * Delete data
     *
     * @return void
     */
    
    public function testRemove()
    {
        global $userId1,$userId2,$inGameInfo1Id,$inGameInfo2Id,
            $inGameInfo3Id, $gameTypesId1,$gameTypesId2;
        
        App\UserIngameInfo::find($inGameInfo1Id)->delete();
        App\UserIngameInfo::find($inGameInfo2Id)->delete();
        App\GameType::find($gameTypesId1)->delete();
        App\GameType::find($gameTypesId2)->delete();
        App\User::find($userId1)->delete();
        App\User::find($userId2)->delete();
        
        $info=App\UserIngameInfo::find($inGameInfo3Id);
        $info->delete();
        /*
        * Delete related autogenerated models also
        */
        $info->user->delete();
        $info->gameType->delete();
    }
}
