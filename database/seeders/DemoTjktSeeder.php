<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\Kelulusan;
use App\Models\Materi;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoTjktSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | User Admin Demo
        |--------------------------------------------------------------------------
        */

        $user = User::updateOrCreate(
            [
                'email' => 'alynoval10@gmail.com',
            ],
            [
                'name' => 'Noval Aly',
                'password' => Hash::make('123456'),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Kelas
        |--------------------------------------------------------------------------
        */

        $kelasX = Kelas::firstOrCreate(
            [
                'nama' => 'X TJKT 1',
            ],
            [
                'tingkat' => 'X',
            ]
        );

        $kelasXI = Kelas::firstOrCreate(
            [
                'nama' => 'XI TJKT 1',
            ],
            [
                'tingkat' => 'XI',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Materi tingkat X
        |--------------------------------------------------------------------------
        */

        $materiX = collect([
            [
                'kode' => 'X-001',
                'nama' => 'Dasar Jaringan',
                'tingkat' => 'X',
            ],
            [
                'kode' => 'X-002',
                'nama' => 'Perakitan Komputer',
                'tingkat' => 'X',
            ],
            [
                'kode' => 'X-003',
                'nama' => 'Sistem Operasi Dasar',
                'tingkat' => 'X',
            ],
        ])->map(function ($data) {
            return Materi::updateOrCreate(
                [
                    'kode' => $data['kode'],
                ],
                [
                    'nama' => $data['nama'],
                    'tingkat' => $data['tingkat'],
                ]
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Materi tingkat XI
        |--------------------------------------------------------------------------
        */

        $materiXI = collect([
            [
                'kode' => 'XI-001',
                'nama' => 'Switching VLAN',
                'tingkat' => 'XI',
            ],
            [
                'kode' => 'XI-002',
                'nama' => 'Internet Gateway',
                'tingkat' => 'XI',
            ],
            [
                'kode' => 'XI-003',
                'nama' => 'Wireless Network',
                'tingkat' => 'XI',
            ],
            [
                'kode' => 'XI-004',
                'nama' => 'Server Debian',
                'tingkat' => 'XI',
            ],
        ])->map(function ($data) {
            return Materi::updateOrCreate(
                [
                    'kode' => $data['kode'],
                ],
                [
                    'nama' => $data['nama'],
                    'tingkat' => $data['tingkat'],
                ]
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Siswa kelas X
        |--------------------------------------------------------------------------
        */

        $siswaX = collect([
            [
                'nis' => '1001',
                'nama' => 'Ahmad Fauzi',
            ],
            [
                'nis' => '1002',
                'nama' => 'Bagas Ramadhan',
            ],
            [
                'nis' => '1003',
                'nama' => 'Citra Lestari',
            ],
            [
                'nis' => '1004',
                'nama' => 'Dimas Saputra',
            ],
            [
                'nis' => '1005',
                'nama' => 'Eka Putri',
            ],
        ])->map(function ($data) use ($kelasX) {
            return Siswa::updateOrCreate(
                [
                    'nis' => $data['nis'],
                ],
                [
                    'nama' => $data['nama'],
                    'kelas_id' => $kelasX->id,
                ]
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Siswa kelas XI
        |--------------------------------------------------------------------------
        */

        $siswaXI = collect([
            [
                'nis' => '1101',
                'nama' => 'Fajar Maulana',
            ],
            [
                'nis' => '1102',
                'nama' => 'Gilang Pratama',
            ],
            [
                'nis' => '1103',
                'nama' => 'Hana Nuraini',
            ],
            [
                'nis' => '1104',
                'nama' => 'Irfan Hakim',
            ],
            [
                'nis' => '1105',
                'nama' => 'Jihan Aulia',
            ],
            [
                'nis' => '1106',
                'nama' => 'Kevin Ramadhan',
            ],
            [
                'nis' => '1107',
                'nama' => 'Laila Safitri',
            ],
            [
                'nis' => '1108',
                'nama' => 'Muhammad Rizki',
            ],
        ])->map(function ($data) use ($kelasXI) {
            return Siswa::updateOrCreate(
                [
                    'nis' => $data['nis'],
                ],
                [
                    'nama' => $data['nama'],
                    'kelas_id' => $kelasXI->id,
                ]
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Penilaian demo kelas XI
        |--------------------------------------------------------------------------
        */

        foreach ($siswaXI as $index => $siswa) {
            foreach ($materiXI as $materiIndex => $materi) {

                /*
                 * Sebagian siswa sengaja belum diuji.
                 */
                if (($index + $materiIndex) % 4 === 0) {
                    continue;
                }

                /*
                 * Variasi nilai 60 - 95.
                 */
                $nilai = 60 + (($index * 7 + $materiIndex * 9) % 36);

                Kelulusan::updateOrCreate(
                    [
                        'siswa_id' => $siswa->id,
                        'materi_id' => $materi->id,
                    ],
                    [
                        'user_id' => $user->id,
                        'tanggal_uji' => now()
                            ->subDays(($index + $materiIndex) % 10)
                            ->format('Y-m-d'),
                        'nilai' => $nilai,
                        'catatan' => $nilai >= 75
                            ? 'Kompetensi sudah dikuasai dengan baik.'
                            : 'Perlu latihan kembali pada bagian konfigurasi.',
                    ]
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Penilaian demo kelas X
        |--------------------------------------------------------------------------
        */

        foreach ($siswaX as $index => $siswa) {
            foreach ($materiX as $materiIndex => $materi) {

                /*
                 * Beberapa dibiarkan belum diuji.
                 */
                if (($index + $materiIndex) % 3 === 0) {
                    continue;
                }

                $nilai = 65 + (($index * 8 + $materiIndex * 7) % 31);

                Kelulusan::updateOrCreate(
                    [
                        'siswa_id' => $siswa->id,
                        'materi_id' => $materi->id,
                    ],
                    [
                        'user_id' => $user->id,
                        'tanggal_uji' => now()
                            ->subDays(($index + $materiIndex) % 7)
                            ->format('Y-m-d'),
                        'nilai' => $nilai,
                        'catatan' => $nilai >= 75
                            ? 'Kompetensi sudah dikuasai dengan baik.'
                            : 'Perlu meningkatkan latihan dan ketelitian.',
                    ]
                );
            }
        }

        $this->command?->info(
            'Data demo TJKT berhasil dibuat.'
        );

        $this->command?->info(
            'Login demo: alynoval10@gmail.com / 123456'
        );
    }
}