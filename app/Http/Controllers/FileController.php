<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/** Berkas privat (legalitas, bukti, dokumentasi) hanya bisa dibuka oleh pengguna yang berhak (NTR-08). */
class FileController extends Controller
{
    public function show(Request $request, string $jenis, int $id)
    {
        $u = $request->user();
        $path = null;

        if ($jenis === 'legalitas') {
            $owner = User::findOrFail($id);
            abort_unless($u->isAdmin() || $u->id === $owner->id, 403);
            $path = $owner->profile()?->dokumen_legalitas;
        } elseif ($jenis === 'bukti') {
            $item = ReportItem::with('report.event.organizer')->findOrFail($id);
            $this->authorizeReport($u, $item->report);
            $path = $item->bukti_berkas;
        } elseif ($jenis === 'dokumentasi') {
            $report = Report::with('event.organizer')->findOrFail($id);
            $this->authorizeReport($u, $report);
            $path = $report->berkas_dokumentasi;
        }

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }

    private function authorizeReport(User $u, Report $report): void
    {
        $event = $report->event;
        $ok = $u->isAdmin()
            || ($u->isOrganizer() && $event->organizer->user_id === $u->id)
            || ($u->isSponsor() && $report->status !== 'Draft' && $u->sponsorProfile
                && $event->commitments()->where('status', 'Diterima')->where('sponsor_id', $u->sponsorProfile->id)->exists());
        abort_unless($ok, 403);
    }
}