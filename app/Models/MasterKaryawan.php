<?php
// MasterKaryawan.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MasterKaryawan extends Model
{
    use HasFactory;
    
    protected $table = 'master_karyawan';
    protected $primaryKey = 'no_npk';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'no_npk',
        'nama',
        'alamat',
    ];

    // PostgreSQL specific casts
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function transaksi()
    {
        return $this->hasMany(TransaksiKoperasi::class, 'npk', 'no_npk');
    }
    
    // Scope untuk pencarian
    public function scopeSearch($query, $term)
    {
        return $query->where('nama', 'ILIKE', "%{$term}%")
                    ->orWhere('no_npk', 'ILIKE', "%{$term}%");
    }
}