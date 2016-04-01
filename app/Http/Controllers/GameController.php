<?php
/**
 *
 * @Ananaskelly
 */
namespace App\Http\Controllers;

use App\GameType;
use App\Http\Requests;
use Illuminate\Http\Request;

class GameController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

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
}
