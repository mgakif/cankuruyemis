<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function cacheClear()
    {
        cache()->flush(); // Tüm cache'i temizle

        return back();
    }
}
