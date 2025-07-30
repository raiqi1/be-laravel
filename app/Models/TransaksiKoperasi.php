<?php
// TransaksiKoperasi.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransaksiKoperasi extends Model
{
    use HasFactory;
    
    protected $table = 'transaksi_koperasis'; 
    
    protected $fillable = [
        'npk',
        'tanggal_transaksi',
        'kode',
        'qty',
        'harga',
        'bayar'
    ];

    // PostgreSQL specific casts
    protected $casts = [
        'tanggal_transaksi' => 'date',
        'qty' => 'integer',
        'harga' => 'decimal:2',
        'bayar' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function karyawan()
    {
        return $this->belongsTo(MasterKaryawan::class, 'npk', 'no_npk');
    }

    public function item()
    {
        return $this->belongsTo(MasterItem::class, 'kode', 'kode');
    }

    // Accessor untuk total
    public function getTotalAttribute()
    {
        return $this->qty * $this->harga;
    }
    
    // Scope untuk filter berdasarkan tanggal
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal_transaksi', [$startDate, $endDate]);
    }
    
    // Scope untuk filter pembayaran
    public function scopePaid($query)
    {
        return $query->where('bayar', true);
    }
    
    public function scopeUnpaid($query)
    {
        return $query->where('bayar', false);
    }
}