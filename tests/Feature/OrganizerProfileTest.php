<?php

namespace Tests\Feature;

use App\Models\OrganizerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrganizerProfileTest extends TestCase
{
    use RefreshDatabase;

    private function createOrganizer(array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'role' => 'organizer',
            'status_verifikasi' => 'Terverifikasi',
        ], $attributes));

        OrganizerProfile::create([
            'user_id' => $user->id,
            'nama_organisasi' => 'BEM Fakultas Teknik',
            'jenis' => 'HIMA/UKM',
            'kontak' => '081234567890',
            'alamat' => 'Gedung Kemahasiswaan Lt. 2',
            'deskripsi' => 'Organisasi mahasiswa penggerak inovasi sosial.',
        ]);

        return $user;
    }

    public function test_guest_cannot_access_organizer_profile(): void
    {
        $response = $this->get(route('organizer.profil'));
        $response->assertRedirect(route('login'));
    }

    public function test_sponsor_cannot_access_organizer_profile(): void
    {
        $sponsor = User::factory()->create([
            'role' => 'sponsor',
        ]);

        $response = $this->actingAs($sponsor)->get(route('organizer.profil'));
        $response->assertForbidden();
    }

    public function test_organizer_can_view_profile_page(): void
    {
        $organizer = $this->createOrganizer();

        $response = $this->actingAs($organizer)->get(route('organizer.profil'));

        $response->assertOk();
        $response->assertSee('Profil Penyelenggara');
        $response->assertSee('BEM Fakultas Teknik');
        $response->assertSee('081234567890');
        $response->assertSee('Organisasi mahasiswa penggerak inovasi sosial.');
    }

    public function test_organizer_can_update_profile_data(): void
    {
        $organizer = $this->createOrganizer();

        $response = $this->actingAs($organizer)->put(route('organizer.profil.update'), [
            'name' => 'Ketua Pelaksana Baru',
            'nama_organisasi' => 'HIMA Teknik Informatika',
            'jenis' => 'HIMA/UKM',
            'kontak' => '089988776655',
            'alamat' => 'Gedung Informatika Lab A',
            'deskripsi' => 'Himpunan mahasiswa program studi teknik informatika.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Profil organisasi berhasil diperbarui.');

        $this->assertDatabaseHas('users', [
            'id' => $organizer->id,
            'name' => 'Ketua Pelaksana Baru',
        ]);

        $this->assertDatabaseHas('organizer_profiles', [
            'user_id' => $organizer->id,
            'nama_organisasi' => 'HIMA Teknik Informatika',
            'jenis' => 'HIMA/UKM',
            'kontak' => '089988776655',
            'alamat' => 'Gedung Informatika Lab A',
            'deskripsi' => 'Himpunan mahasiswa program studi teknik informatika.',
        ]);
    }

    public function test_organizer_can_upload_new_legal_document(): void
    {
        Storage::fake('local');

        $organizer = $this->createOrganizer();
        $file = UploadedFile::fake()->create('sk_organisasi.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($organizer)->put(route('organizer.profil.update'), [
            'name' => 'Nama PIC',
            'nama_organisasi' => 'Komunitas Peduli Lingkungan',
            'jenis' => 'Komunitas',
            'kontak' => '081233221100',
            'alamat' => 'Jl. Hijau Lestari No. 10',
            'deskripsi' => 'Komunitas penggerak aksi lingkungan pesisir.',
            'dokumen' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Profil organisasi berhasil diperbarui.');

        $profile = $organizer->fresh()->organizerProfile;
        $this->assertNotNull($profile->dokumen_legalitas);
        Storage::disk('local')->assertExists($profile->dokumen_legalitas);
    }

    public function test_update_profile_validation_fails_for_invalid_data(): void
    {
        $organizer = $this->createOrganizer();

        $response = $this->actingAs($organizer)->put(route('organizer.profil.update'), [
            'name' => '',
            'nama_organisasi' => '',
            'jenis' => 'InvalidJenis',
            'kontak' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'nama_organisasi', 'jenis', 'kontak']);
    }

    public function test_update_profile_validation_rejects_files_over_5mb(): void
    {
        $organizer = $this->createOrganizer();
        $largeFile = UploadedFile::fake()->create('berkas_besar.pdf', 6000, 'application/pdf'); // > 5MB

        $response = $this->actingAs($organizer)->put(route('organizer.profil.update'), [
            'name' => 'PIC Name',
            'nama_organisasi' => 'Yayasan Amal',
            'jenis' => 'NGO',
            'kontak' => '081234567890',
            'dokumen' => $largeFile,
        ]);

        $response->assertSessionHasErrors(['dokumen']);
    }
}
