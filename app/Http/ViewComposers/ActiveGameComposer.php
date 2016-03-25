<?php
/**
 *
 * @artesby
 */
namespace App\Http\ViewComposers;

use Illuminate\Contracts\View\View;

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
        $currentUser = \Auth::user();
        $view->with('userActiveGame', \App\User::userHasGame($currentUser));
    }
}
