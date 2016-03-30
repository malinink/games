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
    
    protected $userId3;
    protected $inGameInfo3Id;
    protected $gameTypesId3;
    
    protected $trashId1;
    protected $trashId2;
    protected $trashId3;
    protected $trashId4;
    
    /**
     * Prepare for testing
     *
     *  @return void
     */
    public function testPrepare()
    {
        DB::statement('ALTER TABLE game_types AUTO_INCREMENT=1;');
        DB::statement('ALTER TABLE user_ingame_infos AUTO_INCREMENT=1;');
        DB::statement('ALTER TABLE users AUTO_INCREMENT=1;');
    }
    /**
     * Create new data
     *
     *  @return void
     */
    public function testCreate()
    {
        global $userId1, $gameTypesId1,$userId2,$gameTypesId2,
            $inGameInfo1Id,$inGameInfo2Id,$inGameInfo3Id,
            $gameTypesId3,$userId3,$trashId1,$trashId2,$trashId3,$trashId4;
        
        //With links
        
        $gametype1 = factory(App\GameType::class)->create();
        $gametype2 = factory(App\GameType::class)->create();
        $user1 = factory(App\User::class)->create();
        $user2 = factory(App\User::class)->create();
        
        $userId1=$user1->id;
        $userId2=$user2->id;
        $gameTypesId1=$gametype1->id;
        $gameTypesId2=$gametype2->id;
        
        $inGameInfo1= factory(App\UserIngameInfo::class)->make();
        $trashId1=$inGameInfo1->user_id;
        $trashId2=$inGameInfo1->game_type_id;
        $inGameInfo1->user()->associate($userId1)
            ->gameType()->associate($gameTypesId1);
        $inGameInfo1->save();
        
        $inGameInfo2= factory(App\UserIngameInfo::class)->make();
        $trashId3=$inGameInfo2->user_id;
        $trashId4=$inGameInfo2->game_type_id;
        $inGameInfo2->user()->associate($userId2)
            ->gameType()->associate($gameTypesId2);
        $inGameInfo2->save();
       
        $inGameInfo1Id=$inGameInfo1->id;
        $inGameInfo2Id=$inGameInfo2->id;
        
        //Without links
        $inGameInfo3= factory(App\UserIngameInfo::class)->create();
        $userId3=$inGameInfo3->user_id;
        $gameTypesId3=$inGameInfo3->game_type_id;
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
        $this->assertEquals(
            App\UserIngameInfo::find($inGameInfo1Id)->user_id,
            App\UserIngameInfo::find($inGameInfo1Id)->user->id
        );
        $this->assertEquals(
            App\UserIngameInfo::find($inGameInfo1Id)->game_type_id,
            App\UserIngameInfo::find($inGameInfo1Id)->gameType->id
        );
        
        //testing from other side
        $this->assertEquals(
            App\User::find($userId1)->id,
            App\User::find($userId1)->userIngameInfos()->first()->user_id
        );
        $this->assertEquals(
            App\GameType::find($gameTypesId1)->id,
            App\GameType::find($gameTypesId1)->userIngameInfos()
                ->first()->game_type_id
        );
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
        $this->assertEquals(
            App\UserIngameInfo::find($inGameInfo2Id)->user_id,
            App\UserIngameInfo::find($inGameInfo2Id)->user->id
        );
        $this->assertEquals(
            App\UserIngameInfo::find($inGameInfo2Id)->game_type_id,
            App\UserIngameInfo::find($inGameInfo2Id)->gameType->id
        );
        
        //testing from other side
        $this->assertEquals(
            App\User::find($userId2)->id,
            App\User::find($userId2)->userIngameInfos()->first()->user_id
        );
        $this->assertEquals(
            App\GameType::find($gameTypesId2)->id,
            App\GameType::find($gameTypesId2)->userIngameInfos()
                ->first()->game_type_id
        );
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
        $this->assertEquals(
            App\UserIngameInfo::find($inGameInfo3Id)->user_id,
            App\UserIngameInfo::find($inGameInfo3Id)->user->id
        );
        $this->assertEquals(
            App\UserIngameInfo::find($inGameInfo3Id)->game_type_id,
            App\UserIngameInfo::find($inGameInfo3Id)->gameType->id
        );
        
        //testing from other side
        $this->assertEquals(
            App\User::find($userId3)->id,
            App\User::find($userId3)->userIngameInfos()->first()->user_id
        );
        $this->assertEquals(
            App\GameType::find($gameTypesId3)->id,
            App\GameType::find($gameTypesId3)->userIngameInfos()
                ->first()->game_type_id
        );
    }
    /**
     * Delete data
     *
     * @return void
     */
    
    public function testRemove()
    {
        global $userId1,$userId2,$inGameInfo1Id,$inGameInfo2Id,
            $inGameInfo3Id, $gameTypesId1,$gameTypesId2,
            $trashId1,$trashId2,$trashId3,$trashId4;
        
        App\UserIngameInfo::find($inGameInfo1Id)->delete();
        App\UserIngameInfo::find($inGameInfo2Id)->delete();
        App\GameType::find($gameTypesId1)->delete();
        App\GameType::find($gameTypesId2)->delete();
        App\User::find($userId1)->delete();
        App\User::find($userId2)->delete();
        
        App\GameType::find($trashId2)->delete();
        App\GameType::find($trashId4)->delete();
        App\User::find($trashId1)->delete();
        App\User::find($trashId3)->delete();
        
        $info=App\UserIngameInfo::find($inGameInfo3Id);
        $info->delete();
        /*
        * Delete related autogenerated models also
        */
        $info->user->delete();
        $info->gameType->delete();
    }
}
