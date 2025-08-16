<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    function index()  {
        return Inertia::render('Home', [
            'title' => 'Welcome To Home'
        ]);
    }
}
