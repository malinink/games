<?php
/**
 *
 * @artesby
 */
namespace App\Http\ViewComposers;

use Illuminate\Contracts\View\View;
use Auth;
use App\User;

class ActiveGameComposer extends ViewComposer
{
    /**
     * Bind active game indicator to all views
     *
     * @param View $view
     * @return void
     */
    public function compose(View $view)
    {
        if (Auth::check()) {
            $currentUser = Auth::user();
            $view->with([
                'currentGameStatus' => $currentUser->getCurrentGameStatus(),
                'NO_GAME' => User::NO_GAME,
                'SEARCH_GAME' => User::SEARCH_GAME,
                'LIVE_GAME' => User::LIVE_GAME,
                ]);
        }
    }
}
