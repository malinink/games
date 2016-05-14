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
use Auth;
use Carbon\Carbon;
use App\Sockets\PushServerSocket;

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
        if ($this->turnInfos->orderBy('id', 'desc')->last()->user_turn == '0') {
            return Game::WHITE;
        } else {
            return Game::BLACK;
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
        $turn = $data['turn'];
        $figureId = $data['figure'];
        $x = $data['x'];
        $y = $data['y'];
        $eatenFigureId = $data['eatenFigureId'];
        $typeId = $data['typeId'];
        $options = $data['options'];
        
        $gameId=GameType::find($game->id);
        $number_turn=$game->turn_number;
        
        $sendingData = [
            'game' => $game->id,
            'user' => $user->id,
            'turn' => $number_turn+1,
            'prev' => $number_turn,
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
        $turnInfo->turn_number = $number_turn+1;
        $turnInfo->move = intval($figureId.$x.$y);
        $turnInfo->options=intval($options);
        $turnInfo->turn_start_time = ($gameId->time_on_turn)*($number_turn+1);
        $turnInfo->user_turn = $turn;
        $turnInfo->save();
    }
    /**
     * Check turn
     *
     * @param \App\User $user
     * @param \App\Game $game
     * @param int $figureId
     * @param int $x
     * @param int $y
     * @param int $typeId
     *
     * @return bool
     */
    public function turn($user, $game, $figureId, $x, $y, $typeId = null)
    {
        try {
            $event = 'none';
            $options='00';
            $eat = 0;
            $change = 0;
            $gameId = $game->id;
            $userId = $user->id;
            $prevTurn = $game->getLastUserTurn();
            if ($prevTurn) {
                $turn=0;
            } else {
                $turn=1;
            }
            $boardTurn = $game->boardInfos->find($gameId);
            $figureGet = BoardInfo::find($figureId);
            $figureColor = $figureGet->color;
            $haveEatenFigure = BoardInfo::where('x', $x, 'y', $y)->count();
            
            // check if game is live
            if (!is_null($game->time_finished)) {
                throw new Exception("Game have finished already");
            }
            
            // check if user has this game
            if (!is_null($game->usergames->find($userId))) {
                throw new Exception("User hasn't got this game");
            }
            
            // check if it's user's turn
            if (!($game->turn_number+1 === $boardTurn->turn_number)) {
                throw new Exception("Not user turn");
            }
            
            // check color
            if (!($figureColor=== $turn)) {
                throw new Exception("Not user's figure");
            }
            
            // check coordinates
            if (!(($x>0 && $x<9) && ($y>0 && $y<9))) {
                throw new Exception("Figure isn't on board");
            }
            
            //check if we eat something
            if ($haveEatenFigure > 1) {
                $eatenFigure = BoardInfo::where('x', $x, 'y', $y, 'game_id', '!=', $gameId);
                $eatenFigureId = $eatenFigure->id;
                if ($eatenFigure->color != $figureColor) {
                    $eat = 1;
                    $options='0'.$eatenFigureId;
                }
            }
            $data = [
                'game' => $game,
                'user' => $user,
                'turn' => $turn,
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
            return false;
        }
    }
}
