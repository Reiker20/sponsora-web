<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    
    public function index(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $items = $request->user()->notifikasi()->paginate(20);
        $ids = $items->where('status_baca', false)->pluck('id');
        $request->user()->notifikasi()->whereIn('id', $ids)->update(['status_baca' => true]);

        return view('notifications', ['items' => $items, 'unread' => $ids]);
    }
}


