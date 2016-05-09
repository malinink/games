<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Game;
use App\Sockets\PushServerSocket;

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
        $type = $dataJs['name'];
        $data = $dataJs['data'];
        $res = Game::validateTurn($data);
        if ($res[0] === true) {
            PushServerSocket::setDataToServer([
                'name' => $type,
                'data' => $res[1]
            ]);
        }
        return $res[0];
    }
}
