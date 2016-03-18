<?php
/**
 *
 * @artesby
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class AdminController extends Controller
{
    /**
     * Show the admin panel.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Auth::user()->isAdmin == '1') {
            return view('admin/home');
        } else {
            return redirect('home')->with('message', 'You are not admin');
        }
        
    }
}
