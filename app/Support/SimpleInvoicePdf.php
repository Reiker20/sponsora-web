<?php

namespace App\Support;

use App\Models\Commitment;

/**
 * Generator PDF 1.4 mandiri untuk dokumen Invoice Sponsora (tanpa library eksternal).
 */
class SimpleInvoicePdf
{
    public static function generate(Commitment $commitment): string
    {
        $commitment->loadMissing(['event.organizer', 'event.category', 'sponsor']);

        $event = $commitment->event;
        $organizer = $event->organizer;
        $sponsor = $commitment->sponsor;

        $invoiceNo = $commitment->nomor_invoice ?? ('INV-SPO-' . str_pad((string) $commitment->id, 4, '0', STR_PAD_LEFT));
        $validatedAt = ($commitment->tanggal_validasi ?? $commitment->updated_at ?? now())->format('d M Y H:i');
        $submittedAt = ($commitment->tanggal_bukti ?? $commitment->tanggal_pengajuan ?? $commitment->created_at ?? now())->format('d M Y H:i');

        $klaim = (int) ($commitment->nominal_klaim ?? $commitment->nominal);
        $valid = (int) ($commitment->nominal_tervalidasi ?? $commitment->nominal);

        $formatRp = fn (int $amount): string => 'Rp ' . number_format($amount, 0, ',', '.');

        $lines = [];
        // Header banner (Brand #17402F)
        $lines[] = '0.090 0.251 0.184 rg';
        $lines[] = '0 755 595.28 86.89 re f';
        $lines[] = '1 1 1 rg';
        $lines[] = self::text(40, 806, 'F2', 20, 'SPONSORA');
        $lines[] = self::text(40, 784, 'F1', 11, 'INVOICE RESMI VALIDASI DONASI & SPONSORSHIP EVENT');
        $lines[] = self::text(410, 806, 'F2', 11, 'NO. INVOICE');
        $lines[] = self::text(410, 788, 'F1', 10, $invoiceNo);

        // Reset text color to dark charcoal
        $lines[] = '0.12 0.15 0.18 rg';

        // Metadata box
        $lines[] = '0.93 0.95 0.94 rg';
        $lines[] = '40 690 515.28 50 re f';
        $lines[] = '0.12 0.15 0.18 rg';
        $lines[] = self::text(52, 722, 'F2', 9, 'TANGGAL VALIDASI');
        $lines[] = self::text(52, 706, 'F1', 10, $validatedAt . ' WIB');
        $lines[] = self::text(230, 722, 'F2', 9, 'TANGGAL TRANSFER / KLAIM');
        $lines[] = self::text(230, 706, 'F1', 10, $submittedAt . ' WIB');
        $lines[] = self::text(415, 722, 'F2', 9, 'STATUS');
        $lines[] = '0.06 0.45 0.25 rg';
        $lines[] = self::text(415, 706, 'F2', 10, 'TERVERIFIKASI');
        $lines[] = '0.12 0.15 0.18 rg';

        // Parties section
        $lines[] = self::text(40, 662, 'F2', 10, 'PENERIMA DANA (ORGANIZER)');
        $lines[] = self::text(310, 662, 'F2', 10, 'DONATUR (SPONSOR)');
        $lines[] = '0.85 0.87 0.86 RG 0.8 w 40 656 m 285 656 l S 310 656 m 555.28 656 l S';

        $orgBank = $event->hasBankAccount()
            ? "{$event->nama_bank} {$event->nomor_rekening} (a.n. {$event->atas_nama_rekening})"
            : ($event->qris_image ? 'Metode Pembayaran: QRIS Organizer' : '-');

        $y = 640;
        foreach (self::wrap($organizer->nama_organisasi ?? '-', 40) as $part) {
            $lines[] = self::text(40, $y, 'F2', 10, $part);
            $y -= 14;
        }
        $lines[] = self::text(40, $y, 'F1', 9, 'Kontak: ' . ($organizer->kontak ?? '-'));
        $y -= 13;
        foreach (self::wrap($orgBank, 44) as $part) {
            $lines[] = self::text(40, $y, 'F1', 9, $part);
            $y -= 13;
        }

        $ys = 640;
        foreach (self::wrap($sponsor->nama_perusahaan ?? '-', 40) as $part) {
            $lines[] = self::text(310, $ys, 'F2', 10, $part);
            $ys -= 14;
        }
        $lines[] = self::text(310, $ys, 'F1', 9, 'Kontak: ' . ($sponsor->kontak ?? '-'));
        $ys -= 13;
        if ($sponsor->nib) {
            $lines[] = self::text(310, $ys, 'F1', 9, 'NIB: ' . $sponsor->nib);
            $ys -= 13;
        }

        // Event Details
        $ye = min($y, $ys) - 18;
        $lines[] = self::text(40, $ye, 'F2', 10, 'RINCIAN KEGIATAN');
        $lines[] = '0.85 0.87 0.86 RG 0.8 w 40 ' . ($ye - 6) . ' m 555.28 ' . ($ye - 6) . ' l S';
        $ye -= 22;
        $lines[] = self::text(40, $ye, 'F1', 9, 'Nama Kegiatan');
        $lines[] = self::text(155, $ye, 'F2', 10, self::truncate($event->judul ?? '-', 62));
        $ye -= 15;
        $lines[] = self::text(40, $ye, 'F1', 9, 'Kategori / Lokasi');
        $lines[] = self::text(155, $ye, 'F1', 9, ($event->category->nama_kategori ?? '-') . '  |  ' . self::truncate($event->lokasi ?? '-', 45));
        $ye -= 15;
        $lines[] = self::text(40, $ye, 'F1', 9, 'Pelaksanaan');
        $lines[] = self::text(155, $ye, 'F1', 9, $event->tanggal_pelaksanaan?->format('d M Y') ?? '-');

        // Financial Breakdown Table
        $yt = $ye - 28;
        $lines[] = '0.090 0.251 0.184 rg';
        $lines[] = '40 ' . ($yt - 6) . ' 515.28 24 re f';
        $lines[] = '1 1 1 rg';
        $lines[] = self::text(50, $yt + 2, 'F2', 9, 'DESKRIPSI VALIDASI');
        $lines[] = self::text(420, $yt + 2, 'F2', 9, 'NOMINAL');
        $lines[] = '0.12 0.15 0.18 rg';

        $rowY = $yt - 24;
        $lines[] = self::text(50, $rowY, 'F1', 10, 'Bentuk Dukungan: ' . ($commitment->bentuk_dukungan ?: 'Dana Tunai'));
        $lines[] = self::text(420, $rowY, 'F1', 10, '-');

        $rowY -= 20;
        $lines[] = self::text(50, $rowY, 'F1', 10, 'Nominal Klaim Awal (Input Sponsor)');
        $lines[] = self::text(420, $rowY, 'F1', 10, $formatRp($klaim));

        $rowY -= 20;
        $lines[] = self::text(50, $rowY, 'F2', 10, 'Nominal Tervalidasi Organizer (Masuk Mutasi)');
        $lines[] = self::text(420, $rowY, 'F2', 10, $formatRp($valid));

        $rowY -= 12;
        $lines[] = '0.85 0.87 0.86 RG 0.8 w 40 ' . $rowY . ' m 555.28 ' . $rowY . ' l S';

        // Highlighted Total Box
        $boxTop = $rowY - 12;
        $lines[] = '0.93 0.96 0.90 rg';
        $lines[] = '40 ' . ($boxTop - 34) . ' 515.28 34 re f';
        $lines[] = '0.090 0.251 0.184 rg';
        $lines[] = self::text(50, $boxTop - 21, 'F2', 11, 'TOTAL DONASI TERVALIDASI PADA INVOICE');
        $lines[] = self::text(420, $boxTop - 21, 'F2', 12, $formatRp($valid));
        $lines[] = '0.12 0.15 0.18 rg';

        // Notes & Audit Trail
        $yn = $boxTop - 58;
        if ($klaim !== $valid) {
            $lines[] = '0.70 0.28 0.05 rg';
            $lines[] = self::text(40, $yn, 'F2', 9, 'CATATAN PENYESUAIAN NOMINAL:');
            $yn -= 14;
            $msg = "Nominal pada invoice disesuaikan oleh Organizer dari klaim awal {$formatRp($klaim)} menjadi {$formatRp($valid)} sesuai hasil pengecekan bukti transfer.";
            foreach (self::wrap($msg, 92) as $part) {
                $lines[] = self::text(40, $yn, 'F1', 9, $part);
                $yn -= 13;
            }
            $lines[] = '0.12 0.15 0.18 rg';
            $yn -= 6;
        }

        if (filled($commitment->catatan_organizer)) {
            $lines[] = self::text(40, $yn, 'F2', 9, 'PESAN / CATATAN ORGANIZER:');
            $yn -= 14;
            foreach (self::wrap((string) $commitment->catatan_organizer, 92) as $part) {
                $lines[] = self::text(40, $yn, 'F1', 9, $part);
                $yn -= 13;
            }
        }

        // Footer
        $lines[] = '0.45 0.48 0.50 rg';
        $lines[] = '0.85 0.87 0.86 RG 0.6 w 40 70 m 555.28 70 l S';
        $lines[] = self::text(40, 54, 'F1', 8, 'Dokumen ini diterbitkan secara otomatis oleh sistem Sponsora berdasarkan validasi aktual Organizer.');
        $lines[] = self::text(40, 42, 'F1', 8, 'Nilai resmi yang diakui pada progres pendanaan event & laporan pertanggungjawaban adalah Nominal Tervalidasi.');

        $stream = implode("\n", $lines);

        return self::buildPdfDocument($stream);
    }

    private static function buildPdfDocument(string $contentStream): string
    {
        $objects = [
            1 => "<< /Type /Catalog /Pages 2 0 R >>",
            2 => "<< /Type /Pages /Kids [3 0 R] /Count 1 >>",
            3 => "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595.28 841.89] /Resources << /Font << /F1 4 0 R /F2 5 0 R >> >> /Contents 6 0 R >>",
            4 => "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>",
            5 => "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>",
            6 => "<< /Length " . strlen($contentStream) . " >>\nstream\n" . $contentStream . "\nendstream",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0 => 0];

        foreach ($objects as $num => $body) {
            $offsets[$num] = strlen($pdf);
            $pdf .= $num . " 0 obj\n" . $body . "\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n" . $xrefOffset . "\n%%EOF";

        return $pdf;
    }

    private static function text(float $x, float $y, string $font, int $size, string $text): string
    {
        return sprintf("BT /%s %d Tf %.2F %.2F Td (%s) Tj ET", $font, $size, $x, $y, self::escape($text));
    }

    private static function escape(string $text): string
    {
        $clean = preg_replace('/[^\x20-\x7E]/', ' ', str_replace(["\r", "\n", "\t"], ' ', $text)) ?? '';

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $clean);
    }

    private static function truncate(string $text, int $max): string
    {
        return strlen($text) > $max ? substr($text, 0, $max - 3) . '...' : $text;
    }

    /** @return array<int, string> */
    private static function wrap(string $text, int $width): array
    {
        $clean = trim(preg_replace('/\s+/', ' ', $text) ?? '');
        if ($clean === '') {
            return ['-'];
        }

        return explode("\n", wordwrap($clean, $width, "\n", true));
    }
}
