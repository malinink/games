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
        $user=  Auth::user();
        $turnValidatedSuccessfully = Game::turn(
            $user,
            $data['game'],
            $data['figureId'],
            $data['x'],
            $data['y'],
            $data['typeId']
        );

        if ($turnValidatedSuccessfully === true) {
            $answer = json_encode([
                'name' => 'turn',
                'data' =>
                [
                    'state' => 'success'
                ]
            ]);
        } else {
            $answer = json_encode([
                'name' => 'turn',
                'data' =>
                [
                    'state' => 'failed'
                ]
            ]);
        }
        return $answer;
    }
}
