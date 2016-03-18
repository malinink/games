<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class AdminController extends Controller
{
    public function index()
    {
        if (Auth::user()->isAdmin == '1')
        {
            return view('admin/home');
        }
        else 
        {
            redirect('home')->with('message', 'You are not admin');
        }
        
    }
}
