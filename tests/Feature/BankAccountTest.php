<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankAccountTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'status_verifikasi' => 'Disetujui']);
    }

    public function test_non_admin_cannot_open_rekening_page(): void
    {
        $sponsor = User::factory()->create(['role' => 'sponsor', 'status_verifikasi' => 'Disetujui']);

        $this->actingAs($sponsor)->get(route('admin.rekening'))->assertForbidden();
    }

    public function test_admin_can_add_rekening(): void
    {
        $this->actingAs($this->admin())->post(route('admin.rekening.store'), [
            'nama_bank' => 'BCA',
            'nomor_rekening' => '1234567890',
            'atas_nama' => 'Sponsora Platform',
            'is_active' => '1',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('bank_accounts', ['nomor_rekening' => '1234567890', 'is_active' => true]);
    }

    public function test_nomor_rekening_must_be_numeric(): void
    {
        $this->actingAs($this->admin())->post(route('admin.rekening.store'), [
            'nama_bank' => 'BCA',
            'nomor_rekening' => 'ABC-XYZ',
            'atas_nama' => 'Sponsora Platform',
        ])->assertSessionHasErrors('nomor_rekening');
    }

    public function test_admin_can_update_toggle_and_delete_rekening(): void
    {
        $admin = $this->admin();
        $a = BankAccount::create(['nama_bank' => 'BCA', 'nomor_rekening' => '111222333', 'atas_nama' => 'Sponsora', 'is_active' => true]);

        $this->actingAs($admin)->put(route('admin.rekening.update', $a), [
            'nama_bank' => 'Mandiri', 'nomor_rekening' => '444555666', 'atas_nama' => 'Sponsora Baru',
        ])->assertSessionHas('success');
        $this->assertDatabaseHas('bank_accounts', ['id' => $a->id, 'nama_bank' => 'Mandiri']);

        $this->actingAs($admin)->post(route('admin.rekening.toggle', $a));
        $this->assertFalse($a->fresh()->is_active);

        $this->actingAs($admin)->delete(route('admin.rekening.destroy', $a));
        $this->assertDatabaseMissing('bank_accounts', ['id' => $a->id]);
    }

    public function test_only_active_scope_returns_active_accounts(): void
    {
        BankAccount::create(['nama_bank' => 'BCA', 'nomor_rekening' => '111111', 'atas_nama' => 'A', 'is_active' => true]);
        BankAccount::create(['nama_bank' => 'BNI', 'nomor_rekening' => '222222', 'atas_nama' => 'B', 'is_active' => false]);

        $this->assertSame(1, BankAccount::active()->count());
    }
}