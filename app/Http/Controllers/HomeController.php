<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Commitment;
use App\Models\Event;
use App\Models\User;

class HomeController extends Controller
{
    public function index()
    {
        $events = Event::catalog()->withFunding()->with(['organizer', 'category'])->latest()->take(3)->get();

        return view('home', [
            'events' => $events,
            'featured' => $events->first(),
            'categories' => Category::orderBy('nama_kategori')->get(),
            'stats' => [
                'event' => Event::whereIn('status', ['Terverifikasi', 'Selesai', 'Dilaporkan'])->count(),
                'mitra' => User::where('role', 'sponsor')->where('status_verifikasi', 'Disetujui')->count(),
                'dana' => Commitment::where('status', 'Diterima')->sum('nominal'),
                'kategori' => Category::count(),
            ],
        ]);
    }
}
