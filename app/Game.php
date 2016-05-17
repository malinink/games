<?php
/**
 *
 * @author Ananaskelly
 */
namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;
use App\BoardInfo;
use App\TurnInfo;
use App\UserGame;
use Auth;
use Carbon\Carbon;
use App\Sockets\PushServerSocket;
use Exception;

class Game extends Model
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'games';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = array(
        'id',
        'game_type_id',
        'private',
        'time_started',
        'time_finished',
        'winner',
        'bonus'
    );
    /**
     * Constant for user color
     *
     * @var const int
     */
    const WHITE = 0;
    const BLACK = 1;
    /**
     * Disable Timestamps fields
     *
     * @var boolean
     */
    public $timestamps = false;
    
    /**
     * Create BoardInfo
     *
     * @link https://github.com/malinink/games/wiki/Database#figure
     * @return void
     */
    protected function createBoardInfo($figure, $position, $special, $color)
    {
        BoardInfo::create([
            'game_id' => $this->id,
            'figure' => $figure,
            'position' => $position,
            'color' => $color,
            'special' => $special,
            'turn_number' => 0
        ]);
    }
    /**
     * Create BoardInfo models for current game
     *
     * @return void
     */
    public static function init(Game $game)
    {
        for ($j=0; $j<8; $j++) {
            $special = 0;
            if ($j<5) {
                $figure = $j+1;
            } else {
                $figure = 8 - $j;
            };
            if ($j == 0 || $j == 4 || $j == 7) {
                $special = 1;
            }
            $game->createBoardInfo(0, 21+$j, 0, false);
            $game->createBoardInfo(0, 71+$j, 0, true);
            $game->createBoardInfo($figure, 11+$j, $special, false);
            $game->createBoardInfo($figure, 81+$j, $special, true);
        }
        /*
         * add players
         */
        $players = [];
        foreach ($game->userGames as $userGame) {
            $players[] = [
                'login' => $userGame->user->name,
                'id'    => $userGame->user->id,
                'color'  => $userGame->color
            ];
        }
        /**
         * add board infos
         */
        $white = [];
        $black = [];
        foreach ($game->boardInfos as $boradInfo) {
            $boradInfoData = [
                'type'     => $boradInfo->figure,
                'position' => $boradInfo->position,
                'id'       => $boradInfo->id,
            ];
            if ($boradInfo->color) {
                $black[] = $boradInfoData;
            } else {
                $white[] = $boradInfoData;
            }
        }
        /*
         * send init to ZMQ
         */
        PushServerSocket::setDataToServer([
            'name' => 'init',
            'data' => [
               'game' => $game->id,
               'turn' => 0,
               'users' => $players,
               'black' => $black,
               'white' => $white,
            ]
        ]);
    }
    /**
     * Create or modified user game
     *
     * @return Game
     */
    public static function createGame(GameType $gameType, $private, User $user)
    {
        $gameStatus = $user->getCurrentGameStatus();
        switch ($gameStatus) {
            case User::NO_GAME:
                $game = Game::where(['private' => $private, 'game_type_id' => $gameType->id, 'time_started' => null])
                    ->orderBy('id', 'ask')
                    ->first();
                $userGame = new UserGame();
                if ($game === null) {
                    $game = new Game();
                    $game->private = $private;
                    $game->gameType()->associate($gameType);
                    $game->save();
                    $userGame->user()->associate($user);
                    $userGame->game()->associate($game);
                    $userGame->color = (bool)rand(0, 1);
                    $userGame->save();
                } else {
                    $opposite = UserGame::where('game_id', $game->id)->first();
                    $userGame->user()->associate($user);
                    $userGame->game()->associate($game);
                    $userGame->color = !$opposite->color;
                    $userGame->save();
                    $game->update(['time_started' => Carbon::now()]);
                    Game::init($game);
                }
                return $game;
            case User::SEARCH_GAME:
                $lastUserGame = $user->userGames->sortBy('id')->last();
                $game = Game::find($lastUserGame->game_id);
                $game->private = $private;
                $game->gameType()->associate($gameType);
                $game->save();
                return $game;
            default:
                return null;
        }
                        
    }
    
    /**
     *
     * @return void
     */
    public function cancelGame()
    {
        if (is_null($this->time_started)) {
            $this->delete();
        }
    }
    /**
     *
     * @return BoardInfo[]
     */
    public function boardInfos()
    {
        return $this->hasMany('App\BoardInfo');
    }
    
    /**
     *
     * @return TurnInfo[]
     */
    public function turnInfos()
    {
        return $this->hasMany('App\TurnInfo');
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
     * @return GameType
     */
    public function gameType()
    {
        return $this->belongsTo('App\GameType');
    }
    /**
     * Get last user turn (color)
     *
     * @return int
     */
    public function getLastUserTurn()
    {
        $turnInfo = $this->turnInfos->sortBy('id')->last();
        if ($turnInfo === null || $turnInfo->user_turn == '1') {
            return Game::BLACK;
        } else {
            return Game::WHITE;
        }
    }
          /**
     * Send message
     *
     * @param bool $event
     * @param bool $eat
     * @param bool $change
     * @param array $data
     *
     * @return void
     */
    protected function sendMessage($event, $eat, $change, $data)
    {
        $game = $data['game'];
        $user = $data['user'];
        $turnNumber = $data['turn'];
        $figureId = $data['figure'];
        $x = $data['x'];
        $y = $data['y'];
        $eatenFigureId = $data['eatenFigureId'];
        $typeId = $data['typeId'];
        $options = $data['options'];
        $prevTurn = $game->getLastUserTurn();

        //supposed turn
        if ($prevTurn) {
            $turn = false;
        } else {
            $turn = true;
        }
        
        $sendingData = [
            'game' => $game->id,
            'user' => $user->id,
            'turn' => $turnNumber,
            'prev' => $turnNumber-1,
            'move' => [
                        'figure' => $figureId,
                        'x'        => $x,
                        'y'        => $y,
                    ]
            ];
        if ($event!='none') {
            $sendingData['event'] = $event;
        }
        if ($eat) {
            $sendingData['remove'] = ['figure' => $eatenFigureId];
        }
        if ($change) {
            $sendingData['change'] = [
                        'figure' => $figureId,
                        'typeId'   => $typeId
                    ];
        }
        PushServerSocket::setDataToServer([
            'name' => 'turn',
            'data' => $sendingData
        ]);

        $turnInfo = new TurnInfo();
        $turnInfo->game_id = $game->id;
        $turnInfo->turn_number = $turnNumber;
        $turnInfo->move = (int)$figureId.$x.$y;
        if($options!= 'null'){
            $turnInfo->options=(int)$options;
        }
        $turnInfo->turn_start_time = Carbon::now();
        $turnInfo->user_turn = $turn;
        $turnInfo->save();
    }
    
    /**
     *
     * @param BoardInfo $figure
     * @param type $x
     * @param type $y
     */
    protected function checkGameRulesOnFigureMove(BoardInfo $figure, $x, $y)
    {
        
    }

    /**
     * Check turn
     *
     * @param \App\User $user
     * @param int $figureId
     * @param int $x
     * @param int $y
     * @param int $typeId
     *
     * @return bool
     */
    public function turn(User $user, $figureId, $x, $y, $typeId = null)
    {
        try {
            $event = 'none';
            $options = 'null';
            $eat = 0;
            $change = 0;
            $eatenFigureId = null;
            $gameId = $this->id;
            $userId = $user->id;
            $turnNumber = 1;
            $turn = Game::WHITE;

            $turnInfo = $this->turnInfos->sortBy('id')->last();
            if ($turnInfo != null) {
                $turnNumber = $turnInfo->turn_number;
            }
            //current color
            $userGameGet = UserGame::where(['user_id' => $userId, 'game_id' => $gameId])->first();
            $userColor = (int) $userGameGet->color;

            $prevTurn = $this->getLastUserTurn();
            //supposed turn
            if ($prevTurn === Game::WHITE) {
                $turn = Game::BLACK;
            } else {
                $turn = Game::WHITE;
            }

            //current figure and its color
            $figureGet = BoardInfo::find($figureId);
            $figureColor = (int) $figureGet->color;
            
            $eatenFigure = BoardInfo::where(['position' => $x . $y, 'game_id' => $gameId])->count();
            

            // check if game is live
            if (!is_null($this->time_finished)) {
                throw new Exception("Game have finished already");
            }

            // check if user has this game
            if (!is_null($this->usergames->find($userId))) {
                throw new Exception("User hasn't got this game");
            }

            // check if it's user's turn
            if (!($userColor === $turn)) {
                throw new Exception("Not user's turn");
            }

            // check color
            if (!($figureColor === $userColor)) {
                throw new Exception("Not user's figure");
            }

            // check coordinates
            if (!(($x > 0 && $x < 9) && ($y > 0 && $y < 9))) {
                throw new Exception("Figure isn't on board");
            }

            $this->checkGameRulesOnFigureMove($figureGet, $x, $y);


            //check if we eat something
            if ($eatenFigure === 1) {
                if ($eatenFigure->color != $figureColor) {
                    $eatenFigureId = $eatenFigure->id;
                    $eat = 1;
                    $options = '0' . $eatenFigureId;
                } else {
                    throw new Exception("Can't eat :(");
                }
            }

            $data = [
                'game' => $this,
                'user' => $user,
                'turn' => $turnNumber,
                'figure' => $figureId,
                'x' => $x,
                'y' => $y,
                'eatenFigureId' => $eatenFigureId,
                'typeId' => $typeId,
                'options' => $options
            ];
            Game::sendMessage($event, $eat, $change, $data);
            return true;
        } catch (Exception $e) {
            //can see $e if want
            echo $e->getMessage();
            return false;
        }
    }
}
