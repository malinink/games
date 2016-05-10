<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Game;

class TurnController extends BaseController
{
    /**
     * Make turn or not
     *
     * @return boolean
     */
    public function turn(TurnRequest $msg)
    {
        $dataJs = json_decode($msg, true);
        $data = $dataJs['data'];

        return Game::validateTurn($data);
    }
}
