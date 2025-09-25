<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $staffs = User::all(); 
        return view('admin.staff_index', compact('staffs'));
    }
}
