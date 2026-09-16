<?php

namespace Tests\Feature;

use App\Models\{Kelas, Materi, Siswa, Kelulusan, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KartuKompetensiTest extends TestCase
{
    use RefreshDatabase;

    public function test_card_requires_login_and_shows_current_grade_materials(): void
    {
        $user = User::factory()->create();
        $kelas = Kelas::create(['nama' => 'X TJKT', 'tingkat' => 'X']);
        $siswa = Siswa::create(['nis' => '001', 'nama' => 'Ani', 'kelas_id' => $kelas->id]);
        $materi = Materi::create(['kode' => 'A', 'nama' => 'Jaringan', 'tingkat' => 'X']);
        Materi::create(['kode' => 'B', 'nama' => 'Routing Lanjut', 'tingkat' => 'XI']);
        Materi::create(['kode' => 'C', 'nama' => 'Kabel', 'tingkat' => 'X']);
        $url = route('siswa.kartu-kompetensi', $siswa);
        $this->get($url)->assertRedirect(route('filament.admin.auth.login'));
        $record = Kelulusan::create(['siswa_id' => $siswa->id, 'materi_id' => $materi->id, 'nilai' => 0, 'tanggal_uji' => '2026-09-16', 'user_id' => $user->id]);
        $this->actingAs($user)->get($url)->assertOk()->assertSee('001')
            ->assertSee('Remedial')->assertSee('Belum Diuji')->assertDontSee('Routing Lanjut');
        $record->update(['nilai' => 80]);
        $this->get($url)->assertOk()->assertSee('Lulus')->assertDontSee('Remedial');
        $this->get('/siswa/99999/kartu-kompetensi')->assertNotFound();
    }
}
