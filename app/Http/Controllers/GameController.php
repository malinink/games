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
        $gameType = GameType::all('type_name', 'id');
        return view('search', compact('gameTypes'));
    }
    /**
     * Find game with params or create new game
     *
     * @return void
     */
    public function create(SearchGameFormRequest $request)
    {
        $gameType = GameType::where('type_name', '=', $request->type)->first();
        Game::createGame($gameType, $request->status);
        return redirect('/home');
    }
}
