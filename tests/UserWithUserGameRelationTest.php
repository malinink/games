<?php
/*
 *
 * @author learp
 */

use Illuminate\Support\Collection;
use App\UserGame;

class UserWithUserGameTest extends TestCase
{
    /**
     *
     * @var $userGame
     */
    protected $userGame;
    
    public function setUp()
    {
        parent::setUp();
        $this->userGame = factory(UserGame::class)->create();
    }
    
    public function testUserToUserGameIsCollection()
    {
        $userGame = $this->userGame;
        $user = $userGame->user;
        $this->assertTrue($user->userGames instanceof Collection);
    }
    
    public function testUserGameToUserRelation()
    {
        $this->assertEquals($this->userGame->user->id, $this->userGame->user_id);
    }
    
    public function testUserToUserGamesRelation()
    {
        $userGame = $this->userGame;
        $user = $userGame->user;
        $this->assertTrue($user->userGames->contains($userGame));
    }
    
    public function tearDown()
    {
        $userGame = $this->userGame;
        /*
         * Delete related autogenerated models also
         */
        $userGame->user->gameType->delete();
        $userGame->user->delete();
        $userGame->game->gameType->delete();
        $userGame->game->delete();
        $userGame->delete();
        parent::tearDown();
    }
}
