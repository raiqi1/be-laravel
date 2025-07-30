<?php
// 2024_01_01_000003_create_transaksi_koperasis_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_koperasis', function (Blueprint $table) {
            $table->id();
            $table->string('npk', 5);
            $table->date('tanggal_transaksi');
            $table->string('kode', 4);
            $table->integer('qty');
            $table->decimal('harga', 12, 2);
            $table->boolean('bayar')->default(false);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('npk')->references('no_npk')->on('master_karyawan')
                  ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('kode')->references('kode')->on('master_items')
                  ->onUpdate('cascade')->onDelete('cascade');
            
            // PostgreSQL specific indexes
            $table->index('tanggal_transaksi');
            $table->index('bayar');
            $table->index(['npk', 'tanggal_transaksi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_koperasis');
    }
};