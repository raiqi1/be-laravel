<?php
// DatabaseSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            MasterKaryawanSeeder::class,
            MasterItemSeeder::class,
            TransaksiKoperasiSeeder::class,
        ]);
    }
}