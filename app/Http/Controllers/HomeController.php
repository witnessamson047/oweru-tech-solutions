<?php

namespace App\Http\Controllers;

use App\Models\ServiceLine;

class HomeController extends Controller
{
    public function index()
    {
        return view('pages.home', [
            'serviceLines' => ServiceLine::active()->get(),
        ]);
    }
}
