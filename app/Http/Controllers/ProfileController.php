<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use App\User;

class ProfileController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Show public profile page
     *
     * @return \Illuminate\Http\Response
     */
    public function view(Request $request, User $id)
    {
        return view('profile.view');
    }
    
    /**
     * Show public profile page
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, User $id)
    {
        return view('profile.personal');
    }
}
