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
use Illuminate\Support\Collection;

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
    public function getLastUserColor()
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
        $prevColor = $game->getLastUserColor();

        //supposed turn
        if ($prevColor) {
            $currentColor = false;
        } else {
            $currentColor = true;
        }
        
        $boardInfo = BoardInfo::find($figureId);
        $boardInfo->turn_number =  $turnNumber;
        $currentPosition = $boardInfo->position;
        
        $turnInfo = new TurnInfo();
        $turnInfo->game_id = $game->id;
        $turnInfo->turn_number = $turnNumber;
        $turnInfo->move = (int)$currentPosition.$y.$x;
        $turnInfo->options = $options;
        $turnInfo->turn_start_time = Carbon::now();
        $turnInfo->user_turn = $currentColor;
        $turnInfo->save();
        
        $boardInfo->position = (int)$y.$x;
        $boardInfo->save();
        
        $sendingData = [
            'game' => $game->id,
            'user' => $user->id,
            'turn' => $turnNumber,
            'prev' => $turnNumber-1,
            'move' => [
                        [
                            'figure' => $figureId,
                            'x'        => $x,
                            'y'        => $y
                        ]
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
    }
    
    /*
     * swap two elements
     * @param &$x
     * @param &$y
     *
     * @return void
     */
    private function swap(&$x, &$y) {
        $temp = $x;
        $x = $y;
        $y = $temp;
    }
    
    /*
     * @param int $fromX
     * @param int $fromY
     * @param int $toX
     * @param int $toY
     *
     * @return boolean
     */
    private function tryMovePawn($fromX, $fromY, $toX, $toY, $gameId, $color)
    {
        $delta = 1;
        if ($color) {
            $delta = -1;
        }
        
        $pos = $toY * 10 + $toX;
        $count = BoardInfo::where(["game_id"=> $gameId, "position" => $pos])->count();

        //check eat
        if (abs($fromX - $toX) == 1 && $fromY + $delta == $toY) {
            if ($count !== 0) {
                return true;
            } else {
                return false;
            }
        }
        
        if ($count !== 0) {
            return false;
        }
        
        if (abs($fromX - $toX) == 0 && $fromY + $delta == $toY) {
            return true;
        }
        
        if (abs($fromX - $toX) == 0 && $fromY + 2*$delta == $toY) {
            if ($fromY == 2 || $fromY == 7) {
                return true;
            } else {
                return false;
            }
        }
        
        return false;
    }
    
    /*
     * @param int $fromX
     * @param int $fromY
     * @param int $toX
     * @param int $toY
     *
     * @return boolean
     */
    private function tryMoveRook($fromX, $fromY, $toX, $toY, $gameId)
    {
        if ($fromX == $toX) {
            if ($fromY > $toY) {
                $this->swap($fromY, $toY);
            }
            
            for ($i = $fromY + 1; $i < $toY; $i++) {
                $pos = $i * 10 + $fromX;
                $count = BoardInfo::where(["game_id"=> $gameId, "position" => $pos])->count();
                
                if ($count !== 0) {
                    return false;
                }
            }
            return true;
        }
        
        if ($fromY == $toY) {
            if ($fromX > $toX) {
                $this->swap($fromX, $toX);
            }
            
            for ($i = $fromX + 1; $i < $toX; $i++) {
                $pos = $fromY * 10 + $i;
                $count = BoardInfo::where(["game_id"=> $gameId, "position" => $pos])->count();
                if ($count !== 0) {
                    return false;
                }
            }
            
            return true;
        }
        
        return false;
    }
    
    /*
     * @param int $fromX
     * @param int $fromY
     * @param int $toX
     * @param int $toY
     *
     * @return boolean
     */
    private function tryMoveKnight($fromX, $fromY, $toX, $toY)
    {
        if (abs($toX - $fromX) == 2 && abs($toY - $fromY) == 1) {
            return true;
        }
        
        if (abs($toX - $fromX) == 1 && abs($toY - $fromY) == 2) {
            return true;
        }
        
        return false;
    }
    
    /*
     * @param int $fromX
     * @param int $fromY
     * @param int $toX
     * @param int $toY
     *
     * @return boolean
     */
    private function tryMoveBishop($fromX, $fromY, $toX, $toY, $gameId)
    {
        $deltaX = abs($fromX - $toX);
        $deltaY = abs($fromY - $toY);
        if ($deltaX == $deltaY) {
            $flag1 = 1;
            $flag2 = 1;
            if ($fromX > $toX) {
                $flag1 = -1;
            }
            
            if ($fromY > $toY) {
                $flag2 = -1;
            }
            
            for ($i = 1; $i < $deltaX; $i++) {
                $pos = ($fromY + $i * $flag2) * 10 + $fromX + $i * $flag1;
                $count = BoardInfo::where(["game_id"=> $gameId, "position" => $pos])->count();
                if ($count !== 0) {
                    return false;
                }
            }
            return true;
        }
        
        return false;
    }
    
    /*
     * @param int $fromX
     * @param int $fromY
     * @param int $toX
     * @param int $toY
     *
     * @return boolean
     */
    private function tryMoveQueen($fromX, $fromY, $toX, $toY, $gameId)
    {
        return $this->tryMoveBishop($fromX, $fromY, $toX, $toY, $gameId) ||
               $this->tryMoveRook($fromX, $fromY, $toX, $toY, $gameId);
    }
    
    /*
     * @param int $fromX
     * @param int $fromY
     * @param int $toX
     * @param int $toY
     * 
     * @return boolean
     */
    private function tryRoque($fromX, $fromY, $toX, $toY, $color, $gameId)
    {
        $delta = 1;
        if ($toX < $fromX) {
            $delta = -1;
        }
        
        $position = $toY * 10 + $toX + $delta;
        $rook = BoardInfo::where(["figure" => 1, "color" => $color, "position" => $position, "game_id" => $gameId])->get()->first();
        $king = BoardInfo::where(["figure" => 5, "color" => $color, "game_id" => $gameId])->get()->first();
        
        if ($king === null || $rook === null) {
            return false;
        }
        
        if (!($king->special && $rook->special)) {
            return false;
        }
        
        for ($i = 1; $i <= abs($fromX - $toX); $i++) {
            $pos = $fromY * 10 + $fromX + $i * $delta;
            $count = BoardInfo::where(["game_id" => $gameId, "position" => $pos])->count();
            if ($count !== 0) {
                return false;
            }
        }
        
        $oppositeFigures = BoardInfo::where(["color" => (1 - $color), "game_id" => $gameId])->get()->all();
        foreach ($oppositeFigures as $oppositeFigure) {
            if ($this->checkGameRulesOnFigureMove($oppositeFigure, $fromX, $fromY, $gameId, true)) {
                return false;
            }
        }
        
        $king->special = false;
        $rook->special = false;
        $king->save();
        $rook->save();
        return true;
    }


    /*
     * @param int $fromX
     * @param int $fromY
     * @param int $toX
     * @param int $toY
     *
     * @return boolean
     */
    private function tryMoveKing($fromX, $fromY, $toX, $toY, $color, $gameId)
    {
        $deltaX = abs($fromX - $toX);
        $deltaY = abs($fromY - $toY);
        
        if (($deltaX == 3 || $deltaX == 2) && $deltaY == 0) {
            return $this->tryRoque($fromX, $fromY, $toX, $toY, $color, $gameId);
        }
        
        if ($deltaX <= 1 && $deltaY <= 1) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check turn
     * throw Exception if we can't move figure
     *
     * @param BoardInfo $figure
     * @param int $x
     * @param int $y
     * @param int $eatenFigure
     *
     * @return void
     */
    protected function checkGameRulesOnFigureMove(BoardInfo $figure, $x, $y, $gameId, $noExceptions = false)
    {
        $fromX = $figure->position % 10;
        $fromY = ($figure->position - $fromX) / 10;
        if ($fromX == 0 || $fromY == 0) {
            if (!$noExceptions) {
                throw new Exception("Can't move eaten figure");
            } else {
                return false;
            }
        }
        
        $success = false;
        switch ($figure->figure) {
            case 0:
                $success = $this->tryMovePawn($fromX, $fromY, $x, $y, $gameId, $figure->color);
                break;
            case 1:
                $success = $this->tryMoveRook($fromX, $fromY, $x, $y, $gameId);
                break;
            case 2:
                $success = $this->tryMoveKnight($fromX, $fromY, $x, $y, $gameId);
                break;
            case 3:
                $success = $this->tryMoveBishop($fromX, $fromY, $x, $y, $gameId);
                break;
            case 4:
                $success = $this->tryMoveQueen($fromX, $fromY, $x, $y, $gameId);
                break;
            case 5:
                $success = $this->tryMoveKing($fromX, $fromY, $x, $y, $figure->color, $gameId);
                break;
        }
        
        if (!$success && !$noExceptions) {
            throw new Exception("Can't move figure");
        }
        return $success;
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
            $options = null;
            $eat = 0;
            $change = 0;
            $eatenFigureId = null;
            $gameId = $this->id;
            $userId = $user->id;
            $turnNumber = 0;
            $currentColor = Game::WHITE;

            $turnInfo = $this->turnInfos->sortBy('id')->last();
            if ($turnInfo != null) {
                $turnNumber = $turnInfo->turn_number;
            }
            $turnNumber=$turnNumber+1;
            
            //current color of user
            $userGameGet = UserGame::where(['user_id' => $userId, 'game_id' => $gameId])->first();
            $userColor = (int) $userGameGet->color;

            $prevColor = $this->getLastUserColor();
            //supposed turn
            if ($prevColor === Game::WHITE) {
                $currentColor = Game::BLACK;
            } else {
                $currentColor = Game::WHITE;
            }

            //current figure and its color
            $figureGet = BoardInfo::find($figureId);
            $figureColor = (int) $figureGet->color;
            
            $eatenFigure = BoardInfo::where(['position' => $y . $x, 'game_id' => $gameId])->count();
             

            // check if game is live
            if (!is_null($this->time_finished)) {
                throw new Exception("Game have finished already");
            }
            
            // check if user has this game
            if (is_null($this->usergames->where('user_id', $userId))) {
                throw new Exception("User hasn't got this game");
            }

            // check if it's user's turn
            if (!($userColor === $currentColor)) {
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

            $this->checkGameRulesOnFigureMove($figureGet, $x, $y, $gameId);


            //check if we eat something
            if ($eatenFigure === 1) {
                $eatFig = BoardInfo::where(['position' => $y . $x, 'game_id' => $gameId])->first();
                if ($eatFig->color != $figureColor) {
                    $eatenFigureId = $eatFig->id;
                    $eat = 1;
                    $options = $eatenFigureId;
                    $eatFig->position = 0;
                    $eatFig->save();
                } else {
                    throw new Exception("Can't eat :(");
                }
            }

            if ($figureGet->figure == 1 || $figureGet->figure == 5) {
                $figureGet->special = false;
                $figureGet->save();
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
            //var_dump($e->getMessage());
            return false;
        }
    }
}
