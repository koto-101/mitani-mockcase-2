<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $staffs = User::all(); // 例: すべてのユーザーを取得（必要に応じてis_adminフラグなどでフィルタリング）
        return view('admin.staff_index', compact('staffs'));
    }
}
