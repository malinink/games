<?php
/*
 *
 * @author IrenJones
 */

use Illuminate\Support\Collection;
use App\UserIngameInfo;

class UserIngameInfoTest extends TestCase
{
    /*
     *
     * @var UserIngameInfo
     */
    protected $inGameInfo;
   
    public function setUp()
    {
        parent::setUp();
        $this->inGameInfo=factory(UserIngameInfo::class)->create();
    }
    
    public function testUserIngameInfoToGameTypes()
    {
        $inGameInfo=$this->inGameInfo;
        
        $this->assertEquals($inGameInfo->game_type_id, $inGameInfo->gameType->id);
    }
    
    public function testUserIngameInfoToUsers()
    {
        $inGameInfo=$this->inGameInfo;
        
        $this->assertEquals($inGameInfo->user_id, $inGameInfo->user->id);
    }
    
    public function testUsersToUserIngameInfoIsCollection()
    {
        $inGameInfo=$this->inGameInfo;
        $user=$inGameInfo->user;
        
        $this->assertTrue($user->userIngameInfos instanceof Collection);
    }
    
    /*
     *
     * @depends testUsersToUserIngameInfoIsCollection
     */
    public function testUsersToUserIngameInfoRelation()
    {
        $inGameInfo=$this->inGameInfo;
        $user=$inGameInfo->user;
        
        $this->assertTrue($user->userIngameInfos->contains($inGameInfo));
    }
    
    public function testGameTypesToUserIngameInfoIsCollection()
    {
        $inGameInfo=$this->inGameInfo;
        $gameType=$inGameInfo->gameType;
        
        $this->assertTrue($gameType->userIngameInfos instanceof Collection);
    }
    
    /*
     *
     * @depends testGameTypesToUserIngameInfoIsCollection
     */
    public function testGameTypesToUserIngameInfoRelation()
    {
        $inGameInfo=$this->inGameInfo;
        $gameType=$inGameInfo->gameType;
       
        $this->assertTrue($gameType->userIngameInfos->contains($inGameInfo));
    }
   
    public function tearDown()
    {
        $info=$this->inGameInfo;
        $info->delete();
        $info->user->delete();
        $info->gameType->delete();
        
        parent::tearDown();
    }
}
