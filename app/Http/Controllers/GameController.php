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
        $gameTypes = GameType::lists('type_name', 'id');
        return view('search', compact('gameTypes'));
    }
    /**
     * Find game with params or create new game
     *
     * @return void
     */
    public function create(SearchGameFormRequest $request)
    {
        /*
         * Get current user
         */
        $user = Auth::user();
        /*
         * Get id of user games
         */
        $userGames = $user->userGames->pluck('game_id');
        $game = Game::where(['private' => $request->status, 'game_type_id' => $request->type, 'time_started' => null])
                    ->whereNotIn('id', $userGames)->orderBy('id', 'ask')
                    ->first();
        if (is_null($game)) {
            /*
             * Create new Game
             */
            $gameType = GameType::findOrFail($request->type);
            $game = new Game;
            $game->private = $request->status;
            $game->gameType()->associate($gameType);
            $game->save();
            /*
             * Crete new UserGame
             */
            $userGame = new UserGame;
            $userGame->color = '0';
            $userGame->game()->associate($game);
            $userGame->user()->associate($user);
            $userGame->save();
           
        } else {
            /*
             * Create new UserGame
             */
            $userGame = new UserGame;
            $userGame->color = '1';
            $userGame->game()->associate($game);
            $userGame->user()->associate($user);
            $userGame->save();
            /*
             * Update game
             */
            $game->update(['time_started' => Carbon::now()]);
            return(GameType::all());
        
        }
        return redirect('/home');
    }
}
