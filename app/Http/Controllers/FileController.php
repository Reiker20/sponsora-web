<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportItem;
use App\Models\User;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Commitment;

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
        } elseif ($jenis === 'transfer') {
            $payment = Payment::with('commitment.sponsor')->findOrFail($id);
            abort_unless($u->isAdmin() || ($u->isSponsor() && $u->sponsorProfile?->id === $payment->commitment->sponsor_id), 403);
            $path = $payment->bukti_transfer;
        } elseif ($jenis === 'pembayaran') {
            $commitment = Commitment::with(['event.organizer', 'sponsor'])->findOrFail($id);
            $ok = $u->isAdmin()
                || ($u->isOrganizer() && $commitment->event->organizer->user_id === $u->id)
                || ($u->isSponsor() && $commitment->sponsor->user_id === $u->id);
            abort_unless($ok, 403);
            $path = $commitment->bukti_pembayaran;
        }

        // abort_unless($path && Storage::disk('local')->exists($path), 404);

        // return Storage::disk('local')->response($path);
                /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        abort_unless($path && $disk->exists($path), 404);

        return $disk->response($path);
    }

    private function authorizeReport(User $u, Report $report): void
    {
        $event = $report->event;
        $ok = $u->isAdmin()
            || ($u->isOrganizer() && $event->organizer->user_id === $u->id)
            || ($u->isSponsor() && $report->status !== 'Draft' && $u->sponsorProfile
                && $event->commitments()->where('status', 'Diterima')->where('nominal', '>', 0)->where('sponsor_id', $u->sponsorProfile->id)->exists());
        abort_unless($ok, 403);
    }
}