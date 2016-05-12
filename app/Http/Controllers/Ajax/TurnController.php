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
     * @return json
     */
    public function turn(TurnRequest $msg)
    {
        $dataJs = json_decode($msg, true);
        $data = $dataJs['data'];
        return Game::turn($data['game'], $data['figure'], $data['x'], $data['y'], $data['typeId']);
    }
}
