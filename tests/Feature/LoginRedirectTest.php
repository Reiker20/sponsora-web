<?php

namespace Tests\Feature;

use App\Models\OrganizerProfile;
use App\Models\SponsorProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_organizer_login_redirects_to_organizer_dashboard(): void
    {
        $organizer = User::factory()->create([
            'role' => 'organizer',
            'status_verifikasi' => 'Terverifikasi',
            'password' => bcrypt('password123'),
        ]);

        OrganizerProfile::create([
            'user_id' => $organizer->id,
            'nama_organisasi' => 'Komunitas Peduli',
            'jenis' => 'Komunitas',
            'kontak' => '08123456789',
            'alamat' => 'Jl. Merdeka No. 1',
        ]);

        $response = $this->post(route('login'), [
            'email' => $organizer->email,
            'password' => 'password123',
            'role' => 'organizer',
        ]);

        $response->assertRedirect(route('organizer.dashboard'));
        $this->assertAuthenticatedAs($organizer);
    }

    public function test_sponsor_login_redirects_to_sponsor_dashboard(): void
    {
        $sponsor = User::factory()->create([
            'role' => 'sponsor',
            'status_verifikasi' => 'Terverifikasi',
            'password' => bcrypt('password123'),
        ]);

        SponsorProfile::create([
            'user_id' => $sponsor->id,
            'nama_perusahaan' => 'PT Mitra Sejahtera',
            'nib' => '1234567890123',
            'fokus_csr' => 'Pendidikan',
            'kontak' => '08129876543',
        ]);

        $response = $this->post(route('login'), [
            'email' => $sponsor->email,
            'password' => 'password123',
            'role' => 'sponsor',
        ]);

        $response->assertRedirect(route('sponsor.dashboard'));
        $this->assertAuthenticatedAs($sponsor);
    }

    public function test_admin_login_redirects_to_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status_verifikasi' => 'Terverifikasi',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post(route('admin.login'), [
            'email' => $admin->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_admin_can_login_via_general_login_form_and_redirects_to_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status_verifikasi' => 'Terverifikasi',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post(route('login'), [
            'email' => $admin->email,
            'password' => 'password123',
            'role' => 'organizer', // even if default radio was organizer
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_authenticated_user_visiting_login_redirects_to_dashboard(): void
    {
        $organizer = User::factory()->create([
            'role' => 'organizer',
            'status_verifikasi' => 'Terverifikasi',
        ]);

        $response = $this->actingAs($organizer)->get(route('login'));

        $response->assertRedirect(route('organizer.dashboard'));
    }
}
