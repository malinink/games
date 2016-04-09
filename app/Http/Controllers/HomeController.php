<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;

class HomeController extends BaseController
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home.index');
    }
    
    /**
     * Show test websockets panel
     *
     * @return \Illuminate\Http\Response
     */
    public function websockets()
    {
        return view('home.websockets');
    }
}
