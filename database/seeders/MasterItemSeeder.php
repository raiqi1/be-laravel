<?php
// MasterItemSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterItem;
use Illuminate\Support\Facades\DB;

class MasterItemSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET session_replication_role = replica;');
        
        $data = [
            [
                'kode' => 'M001',
                'nama_item' => 'Minyak Goreng 1 kg',
                'harga' => 23000.00,
                'stok' => 50,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'kode' => 'M002',
                'nama_item' => 'Beras 5kg',
                'harga' => 60000.00,
                'stok' => 30,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'kode' => 'M003',
                'nama_item' => 'Gula 1 kg',
                'harga' => 15000.00,
                'stok' => 25,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'kode' => 'M004',
                'nama_item' => 'Mie Goreng',
                'harga' => 2500.00,
                'stok' => 100,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];
        
        MasterItem::upsert($data, ['kode'], ['nama_item', 'harga', 'stok', 'updated_at']);
        
        DB::statement('SET session_replication_role = DEFAULT;');
    }
}
