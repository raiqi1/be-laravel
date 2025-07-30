<?php
// 2024_01_01_000002_create_master_items_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_items', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 4)->unique();
            $table->string('nama_item', 50);
            $table->decimal('harga', 12, 2); // PostgreSQL supports higher precision
            $table->integer('stok')->default(0);
            $table->timestamps();
            
            // PostgreSQL specific indexes
            $table->index('nama_item');
            $table->index('stok');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_items');
    }
};
