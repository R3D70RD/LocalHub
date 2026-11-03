<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class CompteController extends Controller
{

public function show(Request $request): View
{
    return view('account.show', [
        'user' => $request->user(),
        'publications' => $request->user()->publications()->latest()->get(),
    ]);
}
}
