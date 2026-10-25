<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $events = Event::catalog()->withFunding()->with(['organizer', 'category'])
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('judul', 'like', "%$s%")
                ->orWhereHas('organizer', fn ($o) => $o->where('nama_organisasi', 'like', "%$s%"))))
            ->when($request->kategori, fn ($q, $c) => $q->where('category_id', $c))
            ->when($request->dana, fn ($q, $d) => match ($d) {
                'kecil' => $q->where('target_dana', '<', 10_000_000),
                'sedang' => $q->whereBetween('target_dana', [10_000_000, 50_000_000]),
                'besar' => $q->where('target_dana', '>', 50_000_000),
                default => $q,
            })
            ->latest()->paginate(9)->withQueryString();

        return view('events.index', ['events' => $events, 'categories' => Category::orderBy('nama_kategori')->get()]);
    }

    public function show(Event $event, Request $request)
    {
        $user = $request->user();
        $owner = $user && $user->isOrganizer() && $user->organizerProfile?->id === $event->organizer_id;
        abort_unless($event->isPublic() || $owner || ($user && $user->isAdmin()), 404);

        $event->loadMissing(['organizer.user', 'category']);
        $myCommitment = $user?->isSponsor() && $user->sponsorProfile
            ? $event->commitments()->where('sponsor_id', $user->sponsorProfile->id)->latest()->first() : null;

        return view('events.show', compact('event', 'myCommitment'));
    }
}
