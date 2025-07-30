<?php
// MasterItem.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MasterItem extends Model
{
    use HasFactory;
    
    protected $table = 'master_items';
    
    protected $fillable = [
        'kode', 
        'nama_item', 
        'harga', 
        'stok'
    ];
    
    // PostgreSQL specific casts
    protected $casts = [
        'harga' => 'decimal:2',
        'stok' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function transaksi()
    {
        return $this->hasMany(TransaksiKoperasi::class, 'kode', 'kode');
    }
    
    // Scope untuk item dengan stok
    public function scopeWithStock($query)
    {
        return $query->where('stok', '>', 0);
    }
    
    // Scope untuk pencarian
    public function scopeSearch($query, $term)
    {
        return $query->where('nama_item', 'ILIKE', "%{$term}%")
                    ->orWhere('kode', 'ILIKE', "%{$term}%");
    }
}