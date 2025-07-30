<?php
// 2024_01_01_000001_create_master_karyawan_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_karyawan', function (Blueprint $table) {
            $table->string('no_npk', 5)->primary();
            $table->string('nama', 50);
            $table->string('alamat', 50);
            $table->timestamps();
            
            // PostgreSQL specific indexes
            $table->index('nama');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_karyawan');
    }
};