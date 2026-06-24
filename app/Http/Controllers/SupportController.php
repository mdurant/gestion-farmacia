<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class SupportController extends Controller
{
    public function __invoke(): View
    {
        return view('support');
    }
}
