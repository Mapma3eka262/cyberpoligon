<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index()
    {
        return view('test');
    }
    
    public function health()
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now(),
            'version' => '1.0.0',
            'environment' => app()->environment(),
        ]);
    }
}
