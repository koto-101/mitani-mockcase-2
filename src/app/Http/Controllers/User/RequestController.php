<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\StampCorrectionRequest;


class RequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $requests = StampCorrectionRequest::with(['user', 'attendance'])
            ->where('user_id', Auth::id())
            ->when($status === 'pending', fn ($q) => $q->where('status', 'pending'))
            ->when($status === 'approved', fn ($q) => $q->where('status', 'approved'))
            ->orderBy('created_at', 'desc')
            ->get();

        return view('user.request_index', [
            'requests' => $requests,
            'status' => $status,
        ]);
    }
}
