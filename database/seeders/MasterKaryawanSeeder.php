<?php
// MasterKaryawanSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterKaryawan;
use Illuminate\Support\Facades\DB;

class MasterKaryawanSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks untuk PostgreSQL
        DB::statement('SET session_replication_role = replica;');
        
        $data = [
            [
                'no_npk' => '10428',
                'nama' => 'Ari',
                'alamat' => 'Jakarta',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'no_npk' => '19020',
                'nama' => 'Syahrul',
                'alamat' => 'Purbalingga',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'no_npk' => '19999',
                'nama' => 'Budiman',
                'alamat' => 'Subang',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];
        
        // Use upsert untuk PostgreSQL (available in Laravel 8+)
        MasterKaryawan::upsert($data, ['no_npk'], ['nama', 'alamat', 'updated_at']);
        
        // Re-enable foreign key checks
        DB::statement('SET session_replication_role = DEFAULT;');
    }
}