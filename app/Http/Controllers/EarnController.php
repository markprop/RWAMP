<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EarnController extends Controller
{
    /**
     * Display the Earn RWAMP Coin page
     */
    public function index()
    {
        return view('earn.index');
    }
}
