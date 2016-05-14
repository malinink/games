<?php
/**
 *
 * @author Ananaskelly
 */
namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Requests\SearchGameFormRequest;
use Carbon\Carbon;
use Auth;
use App\GameType;
use App\Game;
use App\UserGame;

class GameController extends BaseController
{
    /**
     * Show the form for games params.
     *
     * @return
     */
    public function search()
    {
        $gameTypesColumn = array_column(GameType::all('type_name')->toArray(), 'type_name');
        $gameTypes = array_combine($gameTypesColumn, $gameTypesColumn);
        return view('game.search', compact('gameTypes'));
    }
    /**
     * Find game with params or create new game
     *
     * @return void
     */
    public function create(SearchGameFormRequest $request)
    {
        $gameType = GameType::where('type_name', '=', $request->type)->first();
        $game = Game::createGame($gameType, $request->status, Auth::user());
        if ($game instanceof Game) {
            return redirect()->route('game', ['gameId' => $game->id]);
        }
        /*
         * throw error via flash
         */
        return redirect('/home');
    }
    
    public function game($gameId)
    {
        $user = Auth::user();
        $game = Game::find($gameId);
        /*
         * check permissions
         */
        $players = [null, null];
        /* @var $game Game */
        foreach ($game->userGames as $userGame) {
            /* @var $userGame UserGame */
            $players[(int)$userGame->color] = $userGame->user->id;
        }
        return view('game.game', [
            'gameId' => $game->id,
            'userId' => $user->id,
            'playerWhiteId' => $players[0],
            'playerBlackId' => $players[1],
        ]);
    }
}
