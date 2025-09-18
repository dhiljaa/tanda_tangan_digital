<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $totalDocuments = Auth::user()->documents()->count();
        $recentDocuments = Auth::user()->documents()->latest()->take(5)->get();

        return view('dashboard', compact('totalDocuments', 'recentDocuments'));
    }
}