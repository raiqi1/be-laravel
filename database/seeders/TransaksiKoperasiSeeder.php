<?php
// TransaksiKoperasiSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TransaksiKoperasi;
use App\Models\MasterItem;
use Illuminate\Support\Facades\DB;

class TransaksiKoperasiSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET session_replication_role = replica;');
        
        $data = [
            [
                'npk' => '10428',
                'tanggal_transaksi' => '2019-01-05',
                'kode' => 'M001',
                'qty' => 1,
                'harga' => 23000.00,
                'bayar' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'npk' => '19020',
                'tanggal_transaksi' => '2019-01-12',
                'kode' => 'M002',
                'qty' => 2,
                'harga' => 60000.00,
                'bayar' => false,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'npk' => '19020',
                'tanggal_transaksi' => '2019-01-12',
                'kode' => 'M001',
                'qty' => 2,
                'harga' => 23000.00,
                'bayar' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'npk' => '10428',
                'tanggal_transaksi' => '2019-01-13',
                'kode' => 'M003',
                'qty' => 1,
                'harga' => 15000.00,
                'bayar' => false,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'npk' => '10428',
                'tanggal_transaksi' => '2019-01-13',
                'kode' => 'M004',
                'qty' => 5,
                'harga' => 2500.00,
                'bayar' => false,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'npk' => '10428',
                'tanggal_transaksi' => '2019-01-13',
                'kode' => 'M002',
                'qty' => 2,
                'harga' => 60000.00,
                'bayar' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];

        foreach ($data as $transaksi) {
            // Create transaksi
            TransaksiKoperasi::create($transaksi);
            
            // Update stock item
            MasterItem::where('kode', $transaksi['kode'])
                     ->decrement('stok', $transaksi['qty']);
        }
        
        DB::statement('SET session_replication_role = DEFAULT;');
    }
}