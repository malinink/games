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
        return view('admin/home');
    }
}
